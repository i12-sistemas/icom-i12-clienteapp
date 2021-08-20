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

use App\Models\CaixaDepto;
use App\Models\CaixaDeptoUsuario;

class CaixasDeptoController extends Controller
{
    public function list(Request $request)
    {
      $ret = new RetApiController;
      try {

        $find = isset($request->find) ? utf8_decode($request->find) : null;
        $perpage = isset($request->perpage) ? $request->perpage : 25;
        $ids = null;
        if (isset($request->ids)) {
            $ids = explode(",", $request->ids);
            if (!is_array($ids)) $ids[] = $ids;
            $ids = count($ids) > 0 ? $ids : null;
        }

        $dataset = CaixaDepto::when($find, function ($query, $find) {
                      return $query->Where('depto', 'like', '%'.$find.'%');
                    })
                    ->when($request->ids, function ($query) use ($ids) {
                        return $query->whereIn('caixa.id', $ids);
                    })
                    ->orderBy('depto', 'desc')
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
        $find = isset($id) ? intVal($id) : 0;
        if (!($find>0)) throw new Exception("Nenhum id informado");

        $row = CaixaDepto::find($find);
        if (!$row) throw new Exception("Nenhuma departamento não foi encontrado");

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
            'depto' => [
                    'string',
                    'min:1',
                    'max:50',
                    'required',
                    Rule::unique('caixa_depto')->ignore(isset($request->id) ? intVal($request->id) : 0),
            ],
            'ativo' => ['boolean', 'required']
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
            $row = CaixaDepto::find($id);
            if (!$row) throw new Exception("Nenhuma deparamento encontrado");
        }

      } catch (\Throwable $th) {
        $ret->msg = $th->getMessage();
        return $ret->toJson();
      }

      try {
        DB::beginTransaction();


        if ($action=='add') {
            $row = new CaixaDepto();
            $row->created_usuarioid = $usuario->id;
        }
        $row->depto = $request->depto;
        $row->ativo = $request->ativo;
        $row->updated_usuarioid = $usuario->id;
        $row->save();



        // usuario e permissoes
        if (isset($request->usuarios)) {
            foreach ($request->usuarios as $useritem) {
                $useritem  =(object)$useritem;
                if ($useritem->action == 'delete') {
                    $del = CaixaDeptoUsuario::where('caixadeptoid', $row->id)->where('usuarioid', $useritem->usuarioid)->delete();
                    if (!$del) throw new Exception("Usuário não foi removido!");
                }
                if ($useritem->action == 'insert') {
                    $item = new CaixaDeptoUsuario();
                    $item->caixadeptoid = $row->id;
                    $item->usuarioid = $useritem->usuarioid;
                    $item->created_at = Carbon::now();
                    $item->created_usuarioid = $usuario->id;
                    $ins = $item->save();
                    if (!$ins) throw new Exception("Usuário não foi inserido");
                }
            }
        }
        // usuario e permissoes

        DB::commit();

        $ret->id = $row->id;
        $ret->data = $row->export(true);
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

        $row = CaixaDepto::find($find);
        if (!$row) throw new Exception("Nenhuma departamento encontrado");

        if ($row->caixas) {
            if ($row->caixas->exists())
                throw new Exception('Não é possível excluir esse departamento pois existem caixas associados. Se necessário inative-a!');
        }

      } catch (\Throwable $th) {
        $ret->msg = $th->getMessage();
        return $ret->toJson();
      }


      try {
        DB::beginTransaction();

        $del = $row->delete();

        DB::commit();

        $ret->msg = 'Departamento ' . $row->depto . ' foi excluído!';
        $ret->ok = true;
      } catch (\Throwable $th) {
        DB::rollBack();
        $ret->msg = $th->getMessage();
      }

      return $ret->toJson();
    }
}
