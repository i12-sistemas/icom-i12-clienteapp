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

use App\Models\ManutencaoServicos;

class ManutencaoServicosController extends Controller
{
  public function list(Request $request)
  {

    $ret = new RetApiController;
    try {
      $sortby = isset($request->sortby) ? $request->sortby : 'descricao';
      $descending = isset($request->descending) ? $request->descending : 'asc';
      $showall = isset($request->showall) ? $request->showall : null;
      $find = isset($request->find) ? utf8_decode($request->find) : null;
      $ids = null;
        if (isset($request->ids)) {
            $ids = explode(",", $request->ids);
            if (!is_array($ids)) $ids[] = $ids;
            $ids = count($ids) > 0 ? $ids : null;
        }
      $perpage = isset($request->perpage) ? $request->perpage : 25;
      $dataset = ManutencaoServicos::whereRaw('if(?=1, true, ativo=1)', [$showall])
                    ->when($find, function ($query) use ($find) {
                        return $query->where(function($query2) use ($find) {
                        return $query2->where('descricao', 'like', '%' . '%'.$find.'%');
                        });
                    })
                    ->when($request->ids, function ($query) use ($ids) {
                        return $query->whereIn('id', $ids);
                    })
                    ->orderBy($sortby, ($descending == 'desc' ? 'desc' : 'asc'))
                    ->orderby('descricao', 'desc')
                    ->paginate($perpage);
      $dados = [];
      foreach ($dataset as $manut) {
          $dados[] = $manut->toObject(true);//showCompact
          // $dados[] = $coleta->toArray();//showCompact
      }
      $ret->data = $dados;
      $ret->sortby = $sortby;
      $ret->descending = ($descending == 'desc' ? 'desc' : 'asc');
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

      $dataset = ManutencaoServicos::find($find);
      if (!$dataset) throw new Exception("Servi??o n??o foi encontrada");

      $ret->data = $dataset->toObject(True);
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
          'descricao' => [
                  'string',
                  'min:1',
                  'max:100',
                  'required',
                  Rule::unique('manutencaoservicos')->ignore(isset($request->id) ? intVal($request->id) : 0),
          ],
          'proxmanut_dias' => ['integer', 'required', 'min:0', 'max:2147483647'],
          'proxmanut_km' => ['integer', 'required', 'min:0', 'max:2147483647'],
          'ativo' => ['boolean', 'required']
      ];
      $messages = [
          'max' => 'O campo :attribute n??o pode ser maior do que :max.',
          'min' => 'O campo :attribute n??o pode ser menor do que :min.',
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
          $row = ManutencaoServicos::find($id);
          if (!$row) throw new Exception("Nenhuma servi??o encontrado");
      }

    } catch (\Throwable $th) {
      $ret->msg = $th->getMessage();
      return $ret->toJson();
    }

    try {
      DB::beginTransaction();


      if ($action=='add') {
          $row = new ManutencaoServicos();
          $row->created_usuarioid = $usuario->id;
      }
      $row->descricao = $request->descricao;
      $row->proxmanut_km = $request->proxmanut_km;
      $row->proxmanut_dias = $request->proxmanut_dias;
      $row->ativo = $request->ativo;
      $row->updated_usuarioid = $usuario->id;
      $row->save();

      DB::commit();

      $ret->id = $row->id;
      $ret->data = $row->toObject(true);
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

      $row = ManutencaoServicos::find($find);
      if (!$row) throw new Exception("Nenhum servi??o encontrado");

      if ($row->manutencoes) {
          if ($row->manutencoes->exists())
              throw new Exception('N??o ?? poss??vel excluir esse servi??o  pois existem manuten????es associadas. Se necess??rio inative-a!');
      }

    } catch (\Throwable $th) {
      $ret->msg = $th->getMessage();
      return $ret->toJson();
    }


    try {
      DB::beginTransaction();

      $del = $row->delete();

      DB::commit();

      $ret->msg = 'Servi??o ' . $row->descricao . ' foi exclu??do!';
      $ret->ok = true;
    } catch (\Throwable $th) {
      DB::rollBack();
      $ret->msg = $th->getMessage();
    }

    return $ret->toJson();
  }


}
