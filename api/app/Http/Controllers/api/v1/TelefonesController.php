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

use App\Models\Telefones;


class TelefonesController extends Controller
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

        $dataset = Telefones::when($request->find, function ($query, $find) {
                      return $query->Where('telefone', 'like','%'. $find.'%')
                                ->orWhere('contato', 'like','%'. $find . '%')
                                ->orWhere('categ', 'like', '%'.$find.'%');
                    })
                    ->when($request->ids, function ($query) use ($ids) {
                        return $query->whereIn('id', $ids);
                    })
                    ->orderBy('categ')
                    ->orderBy('nordem')
                    ->orderBy('contato')
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

        $row = Telefones::find($find);
        if (!$row) throw new Exception("Nenhum telefone encontrado!");

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
        if (!$usuario) throw new Exception('Nenhum usu??rio autenticado');

        $rules = [
            'contato' => [ 'string', 'min:1', 'max:75', 'required' ],
            'telefone' => [ 'string', 'min:1', 'max:45', 'required',
                    Rule::unique('telefones')->ignore(isset($request->id) ? intVal($request->id) : 0),
            ],
            'nordem' => ['integer', 'required', 'min:0']
        ];
        $messages = [
            'size' => 'O campo :attribute, dever?? ter :max caracteres.',
            'integer' => 'O conteudo do campo :attribute dever?? ser um n??mero inteiro.',
            'unique' => 'O conteudo do campo :attribute j?? foi cadastrado.',
            'required' => 'O conteudo do campo :attribute ?? obrigat??rio.',
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
            $row = Telefones::find($id);
            if (!$row) throw new Exception("Nenhum telefone encontrado");
        }

      } catch (\Throwable $th) {
        $ret->msg = $th->getMessage();
        return $ret->toJson();
      }

      try {
        DB::beginTransaction();


        if ($action=='add') {
            $row = new Telefones();
            $row->created_usuarioid = $usuario->id;
        }
        $row->telefone = $request->telefone;
        $row->contato = $request->contato;
        $row->categ = $request->categ;
        $row->icon = $request->icon;
        $row->nordem = $request->nordem;
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

        $row = Telefones::find($find);
        if (!$row) throw new Exception("Nenhum telefone encontrado");

      } catch (\Throwable $th) {
        $ret->msg = $th->getMessage();
        return $ret->toJson();
      }


      try {
        DB::beginTransaction();

        $del = $row->delete();

        DB::commit();

        $ret->msg = 'Telefone ' . $row->telefone . ' foi exclu??do!';
        $ret->ok = true;
      } catch (\Throwable $th) {
        DB::rollBack();
        $ret->msg = $th->getMessage();
      }

      return $ret->toJson();
    }
}
