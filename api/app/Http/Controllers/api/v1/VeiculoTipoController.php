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

use App\Models\VeiculoTipo;

class VeiculoTipoController extends Controller
{
    public function list(Request $request)
    {
      $ret = new RetApiController;
      try {
        $find = isset($request->find) ? utf8_decode($request->find) : null;
        $perpage = isset($request->perpage) ? $request->perpage : 25;
        $dataset = VeiculoTipo::when($find, function ($query, $find) {
                      return $query->Where('tipo', 'like','%'. $find.'%');
                    })
                    ->orderBy('tipo')
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

    // public function find(Request $request, $id)
    // {
    //   $ret = new RetApiController;
    //   try {
    //     $find = isset($id) ? intVal($id) : 0;
    //     if (!($find>0)) throw new Exception("Nenhum id informado");

    //     $cidade = Cidades::find($find);
    //     if (!$cidade) throw new Exception("Cidade não foi encontrada");

    //     $ret->data = $cidade->toObject(True);
    //     $ret->ok = true;

    //   } catch (\Throwable $th) {
    //     $ret->msg = $th->getMessage();
    //   }
    //   return $ret->toJson();
    // }


    public function save(Request $request)
    {
      $ret = new RetApiController;
      try {

        $usuario = session('usuario');
        if (!$usuario) throw new Exception('Nenhum usuário autenticado');

        $rules = [
            'tipo' => [
                    'string',
                    'max:60',
                    'required',
                    Rule::unique('veiculo_tipo')->ignore(isset($request->id) ? intVal($request->id) : 0),
                ]
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
            $veiculotipo = VeiculoTipo::find($id);
            if (!$veiculotipo) throw new Exception("Tipo de veículo não foi encontrado");
        }

      } catch (\Throwable $th) {
        $ret->msg = $th->getMessage();
        return $ret->toJson();
      }

      try {
        DB::beginTransaction();


        if ($action=='add') {
            $veiculotipo = new VeiculoTipo();
            $veiculotipo->created_usuarioid = $usuario->id;
            $veiculotipo->ativo = 1;
        }
        if (isset($request->ativo)) $veiculotipo->ativo = $request->ativo;
        $veiculotipo->tipo = $request->tipo;
        $veiculotipo->updated_usuarioid = $usuario->id;
        $veiculotipo->save();

        DB::commit();

        $ret->id = $veiculotipo->id;
        $ret->data = $veiculotipo->toObject(false);
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

        $veiculotipo = VeiculoTipo::find($find);
        if (!$veiculotipo) throw new Exception("Tipo de veículo não foi encontrado");

        $count = $veiculotipo->veiculos->count();
        if ($count>0)
            throw new Exception($count==1 ? 'Existe um veículo associado' :  "Existem " . $count . " veículos associados");

      } catch (\Throwable $th) {
        $ret->msg = $th->getMessage();
        return $ret->toJson();
      }


      try {
        DB::beginTransaction();

        $del = $veiculotipo->delete();

        DB::commit();

        $ret->msg = 'Tipo de veículo ' . $veiculotipo->tipo . ' foi excluído!';
        $ret->ok = true;
      } catch (\Throwable $th) {
        DB::rollBack();
        $ret->msg = $th->getMessage();
      }

      return $ret->toJson();
    }

}
