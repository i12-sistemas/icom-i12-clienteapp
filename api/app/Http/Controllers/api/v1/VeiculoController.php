<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\DB;
use Validator;
use Carbon\Carbon;
use Illuminate\Validation\Rule;

use App\Http\Controllers\RetApiController;

use App\Models\Veiculo;
use App\Models\AcertoViagem;


class VeiculoController extends Controller
{
    public function list(Request $request)
    {
      $ret = new RetApiController;
      try {
        $find = isset($request->find) ? utf8_decode($request->find) : null;
        $perpage = isset($request->perpage) ? $request->perpage : 25;
        $proprietario = isset($request->proprietario) ? $request->proprietario : null;
        $showall = isset($request->showall) ? boolval($request->showall) : false;
        $ids = null;
        if (isset($request->ids)) {
            $ids = explode(",", $request->ids);
            if (!is_array($ids)) $ids[] = $ids;
            $ids = count($ids) > 0 ? $ids : null;
        }
        $manutencaoprioridade = null;
        if (isset($request->manutencao)) {
            $manutencaoprioridade = explode(",", $request->manutencao);
            if (!is_array($manutencaoprioridade)) $manutencaoprioridade[] = $manutencaoprioridade;
            $manutencaoprioridade = count($manutencaoprioridade) > 0 ? $manutencaoprioridade : null;
        }

        $dataset = Veiculo::select(DB::raw('veiculo.*'))
                    ->leftJoin('veiculo_alertamanut', 'veiculo.alertamanutid', '=', 'veiculo_alertamanut.id')
                    ->with('cidade', 'tipo', 'created_usuario', 'updated_usuario', 'alertamanut')
                    ->whereRaw('if(?=1, true, veiculo.ativo=1)', [$showall])
                    ->when($find, function ($query, $find) {
                      return $query->Where('veiculo.descricao', 'like', '%' . $find.'%')
                        ->orWhere('veiculo.placa', 'like', '%' . cleanDocMask($find) .'%');
                    })
                    ->when(isset($request->proprietario), function ($query, $t) use ($proprietario) {
                      return $query->Where('veiculo.proprietario', '=', $proprietario);
                    })
                    ->when(isset($request->manutencao), function ($query, $t) use ($manutencaoprioridade) {
                        return $query->whereIn('veiculo_alertamanut.prioridade', $manutencaoprioridade);
                      })
                    ->when($request->ids, function ($query) use ($ids) {
                        return $query->whereIn('veiculo.id', $ids);
                    })
                    ->orderBy('veiculo.placa')
                    ->paginate($perpage);
        $dados = [];
        foreach ($dataset as $row) {
            $dados[] = $row->toObject();
        }
        $ret->data = $dados;
        $ret->collection = $dataset;
        $ret->ok = true;

      } catch (\Throwable $th) {
        $ret->msg = $th->getMessage();
      }
      return $ret->toJson();
    }

    public function find(Request $request, $id)
    {
      $ret = new RetApiController;
      try {
        $find = isset($id) ? intVal($id) : 0;
        if (!($find>0)) throw new Exception("Nenhum id informado");

        $veiculo = Veiculo::find($find);
        if (!$veiculo) throw new Exception("Nenhum registro encontrado");

        $ret->data = $veiculo->toObject(True);
        $ret->ok = true;

      } catch (\Throwable $th) {
        $ret->msg = $th->getMessage();
      }
      return $ret->toJson();
    }


    public function save(Request $request)
    {
      $ret = new RetApiController;
      try {

        $usuario = session('usuario');
        if (!$usuario) throw new Exception('Nenhum usuário autenticado');

        $rules = [
            'placa' => [
                    'string',
                    'min:5',
                    'max:7',
                    'required',
                    Rule::unique('veiculo')->ignore(isset($request->id) ? intVal($request->id) : 0),
            ],
            'proprietario' => ['string', 'size:1', 'required'],
            'descricao' => ['string', 'max:60', 'required'],
            'ativo' => ['boolean', 'required'],
            'cidadeid' => ['integer'],
            'tipoid' => ['integer']
        ];
        $messages = [
            'size' => 'O campo :attribute, deverá ter :max caracteres.',
            'integer' => 'O conteudo do campo :attribute deverá ser um número inteiro.',
            'unique' => 'O conteudo do campo :attribute já foi cadastrado.',
            'required' => 'O conteudo do campo :attribute é obrigatório.',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
            $msgs = [];
            $errors = $validator->errors();
            foreach ($errors->all() as $message) {
                $msgs[] = $message;
            }
            $ret->data = $msgs;
            throw new Exception(join("; ", $msgs));
        }

        $id = isset($request->id) ? intVal($request->id) : 0;
        $action =  $id>0 ? 'update' : 'add';

        if ($action=='update') {
            $veiculo = Veiculo::find($id);
            if (!$veiculo) throw new Exception("Veículo não foi encontrado");
        }

      } catch (\Throwable $th) {
        $ret->msg = $th->getMessage();
        return $ret->toJson();
      }

      try {
        DB::beginTransaction();


        if ($action=='add') {
            $veiculo = new Veiculo();
            $veiculo->created_usuarioid = $usuario->id;
            $veiculo->mediaconsumo = 0;
        }
        $veiculo->descricao = $request->descricao;
        $veiculo->placa = $request->placa;

        if ($request->has('mediaconsumo')) $veiculo->mediaconsumo = $request->mediaconsumo;
        $veiculo->tara = $request->tara;
        $veiculo->lotacao = $request->lotacao;
        $veiculo->pbt = $request->pbt;
        $veiculo->pbtc = $request->pbtc;

        $veiculo->ativo = $request->ativo;
        $veiculo->proprietario = $request->proprietario;
        if (isset($request->tipoid)) $veiculo->tipoid = $request->tipoid;
        if (isset($request->cidadeid)) $veiculo->cidadeid = $request->cidadeid;
        $veiculo->updated_usuarioid = $usuario->id;
        $veiculo->save();



        DB::commit();

        $ret->id = $veiculo->id;
        $ret->data = $veiculo->toObject(false);
        $ret->ok = true;

      } catch (\Throwable $th) {
        DB::rollBack();
        $ret->msg = $th->getMessage();
      }


      return $ret->toJson();
    }


    public function delete(Request $request, $id)
    {
      $ret = new RetApiController;
      try {
        $find = isset($id) ? intVal($id) : 0;
        if (!($find>0)) throw new Exception("Nenhum id informado");

        $veiculo = Veiculo::find($find);
        if (!$veiculo) throw new Exception("Veículo não foi encontrado");

        // $count = $veiculotipo->veiculos->count();
        // if ($count>0)
        //     throw new Exception($count==1 ? 'Existe um veículo associado' :  "Existem " . $count . " veículos associados");

      } catch (\Throwable $th) {
        $ret->msg = $th->getMessage();
        return $ret->toJson();
      }


      try {
        DB::beginTransaction();

        $del = $veiculo->delete();

        DB::commit();

        $ret->msg = 'Veículo placa ' . $veiculo->placa . ' foi excluído!';
        $ret->ok = true;
      } catch (\Throwable $th) {
        DB::rollBack();
        $ret->msg = $th->getMessage();
      }

      return $ret->toJson();
    }


    public function ultimokm_acerto(Request $request, $id)
    {
      $ret = new RetApiController;
      try {
        $find = isset($id) ? intVal($id) : 0;
        if (!($find>0)) throw new Exception("Nenhum id informado");

        $kmfim = AcertoViagem::where('status', '=', 1)
                        ->where('veiculoid', '=', $id)
                        ->max('kmfim');

        if (!$kmfim) $kmfim = 0;

        $ret->data = $kmfim;
        $ret->ok = true;

      } catch (\Throwable $th) {
        $ret->msg = $th->getMessage();
      }
      return $ret->toJson();
    }


}
