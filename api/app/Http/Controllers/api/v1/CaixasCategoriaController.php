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

use App\Models\CaixaCategoria;

class CaixasCategoriaController extends Controller
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

        $dataset = CaixaCategoria::when($find, function ($query, $find) {
                      return $query->Where('descricao', 'like', '%'.$find.'%');
                    })
                    ->when($request->ids, function ($query) use ($ids) {
                        return $query->whereIn('id', $ids);
                    })
                    ->orderBy('descricao', 'asc')
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

        $row = CaixaCategoria::find($find);
        if (!$row) throw new Exception("Nenhuma categoria não foi encontrado");

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
            'descricao' => [
                    'string',
                    'min:1',
                    'max:50',
                    'required',
                    Rule::unique('caixa_categoria')->ignore(isset($request->id) ? intVal($request->id) : 0),
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
            $row = CaixaCategoria::find($id);
            if (!$row) throw new Exception("Nenhuma categoria encontrada");
        }

      } catch (\Throwable $th) {
        $ret->msg = $th->getMessage();
        return $ret->toJson();
      }

      try {
        DB::beginTransaction();


        if ($action=='add') {
            $row = new CaixaCategoria();
            $row->created_usuarioid = $usuario->id;
        }
        $row->descricao = $request->descricao;
        $row->ativo = $request->ativo;
        $row->updated_usuarioid = $usuario->id;
        $row->save();

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

        $row = CaixaCategoria::find($find);
        if (!$row) throw new Exception("Nenhuma categoria encontrada");

        if ($row->caixas) {
            if ($row->caixas->exists())
                throw new Exception('Não é possível excluir essa categoria pois existem caixas associados. Se necessário inative-a!');
        }

      } catch (\Throwable $th) {
        $ret->msg = $th->getMessage();
        return $ret->toJson();
      }


      try {
        DB::beginTransaction();

        $del = $row->delete();

        DB::commit();

        $ret->msg = 'Categoria ' . $row->descricao . ' foi excluída!';
        $ret->ok = true;
      } catch (\Throwable $th) {
        DB::rollBack();
        $ret->msg = $th->getMessage();
      }

      return $ret->toJson();
    }
}
