<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Exception;
use Illuminate\Support\Facades\DB;
use Validator;
use Carbon\Carbon;
use Illuminate\Validation\Rule;

use App\Http\Controllers\RetApiController;

use App\Models\FollowupErros;

class FollowupErrosController extends Controller
{

    public function list(Request $request)
    {
      $ret = new RetApiController;
      try {
        $find = isset($request->find) ? utf8_decode($request->find) : null;
        $tipo = isset($request->tipo) ? utf8_decode($request->tipo) : null;
        $showall = isset($request->showall) ? boolval($request->showall) : false;
        $perpage = isset($request->perpage) ? $request->perpage : 25;
        $ids = null;
        if (isset($request->ids)) {
            $ids = explode(",", $request->ids);
            if (!is_array($ids)) $ids[] = $ids;
            $ids = count($ids) > 0 ? $ids : null;
        }

        $dataset = FollowupErros::whereRaw('if(?=1, true, followup_erros.ativo=1)', [$showall])
                    ->when($find, function ($query, $find) {
                      return $query->Where('descricao', 'like', '%'.$find.'%');
                    })
                    ->when(isset($request->tipo), function ($query) use ($tipo) {
                      return $query->Where('followup_erros.tipo', '=', $tipo);
                    })
                    ->when($request->ids, function ($query) use ($ids) {
                        return $query->whereIn('id', $ids);
                    })
                    ->orderBy('descricao')
                    ->paginate($perpage);
        $dados = [];
        foreach ($dataset as $row) {
            $dados[] = $row->export(false);
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
        $find = isset($id) ? $id : null;
        if (!$find) throw new Exception("Nenhum id informado");
        if ($find  == '') throw new Exception("Nenhum id informado");

        $row = FollowupErros::find($find);
        if (!$row) throw new Exception("Nenhum registro encontrado");

        $ret->data = $row->export(True);
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
                'descricao' => ['required', 'min:1', 'max:250'],
                'tipo' => ['required', 'string']
            ];
            $messages = [
                'size' => 'O campo :attribute, deverá ter :max caracteres.',
                'integer' => 'O conteudo do campo :attribute deverá ser um número inteiro.',
                'cpf.unique' => 'O conteudo do campo :attribute já foi cadastrado e está ativo.',
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
                $row = FollowupErros::find($id);
                if (!$row) throw new Exception("Registro não foi encontrado");
            }

        } catch (\Throwable $th) {
            $ret->msg = $th->getMessage();
            return $ret->toJson();
        }

        try {
            DB::beginTransaction();


            if ($action=='add') {
                $row = new FollowupErros();
                $row->created_usuarioid = $usuario->id;
            }
            $row->descricao = $request->descricao;
            $row->tipo = $request->tipo;
            $row->ativo = $request->ativo;
            $row->updated_usuarioid = $usuario->id;
            $row->save();

            DB::commit();

            $ret->id = $row->id;
            $ret->data = $row->export(True);
            $ret->msg = $action;
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

            $row = FollowupErros::find($id);
            if (!$row) throw new Exception("Registro não foi encontrado");

            $fups = $row->followup->count();
            if ($fups>0)
                throw new Exception($fups==1 ? 'Existe um Follow Up associado' :  "Existem " . $fups . " Follow Ups associados");

        } catch (\Throwable $th) {
            $ret->msg = $th->getMessage();
            return $ret->toJson();
        }


        try {
            DB::beginTransaction();

            $del = $row->delete();

            DB::commit();

            $ret->msg = 'Registro ' . $row->descricao . ' foi excluído!';
            $ret->ok = true;
        } catch (\Throwable $th) {
            DB::rollBack();
            $ret->msg = $th->getMessage();
        }

        return $ret->toJson();
    }
}
