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

use App\Models\Produto;

class ProdutoController extends Controller
{
  public function list(Request $request)
  {
    $ret = new RetApiController;
    try {
      $find = isset($request->find) ? utf8_decode($request->find) : null;
      $perpage = isset($request->perpage) ? $request->perpage : 25;
      $produtos = Produto::when($find, function ($query, $find) {
                    $onufind = intVal($find);
                    return $query->Where('nome', 'like', '%'.$find.'%')
                        //   ->orWhere('classerisco', 'like', $find.'%')
                        //   ->orWhere('riscosubs', 'like', $find.'%')
                        //   ->orWhere('numrisco', 'like', $find.'%')
                        //   ->orWhere('grupoemb', 'like', $find.'%')
                        //   ->orWhere('provespec', 'like', $find.'%')
                        //   ->orWhere('qtdeltdav', 'like', $find.'%')
                        //   ->orWhere('embibcinst', 'like', $find.'%')
                        //   ->orWhere('embibcprov', 'like', $find.'%')
                        //   ->orWhere('tanqueprov', 'like', $find.'%')
                        //   ->orWhere('kit', 'like', $find.'%')
                        //   ->orWhere('epi', 'like', $find.'%')
                        //   ->orWhere('polimeriza', 'like', $find.'%')
                          ->orWhereRaw('if(?>0, onu=?, false)', [$onufind, $onufind]);
                  })
                  ->orderBy('nome')
                  ->orderby('onu')
                  ->paginate($perpage);
      $ret->collection = $produtos;
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

      $produto = Produto::find($find);
      if (!$produto) throw new Exception("Produto não foi encontrada");

      $ret->data = $produto->toObject(false);
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
          'onu' => ['integer'],
          'nome' => ['required', 'min:2', 'max:255'],
          'classerisco' => ['max:50'],
          'riscosubs' => ['max:50'],
          'riscosubs2' => ['max:50'],
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
          $produto = Produto::find($id);
          if (!$produto) throw new Exception("Produto não foi encontrado");
      }

    } catch (\Throwable $th) {
      $ret->msg = $th->getMessage();
      return $ret->toJson();
    }

    try {
      DB::beginTransaction();


      if ($action=='add') {
          $produto = new Produto();
          $produto->created_usuarioid = $usuario->id;
      }
      $produto->onu = $request->onu;
      $produto->nome = $request->nome;
      if (isset($request->classerisco)) $produto->classerisco = $request->classerisco;
      if (isset($request->riscosubs)) $produto->riscosubs = $request->riscosubs;
      if (isset($request->riscosubs2)) $produto->riscosubs2 = $request->riscosubs2;
      $produto->reage_agua = $request->reage_agua;
      $produto->ativo = $request->ativo;
      $produto->updated_usuarioid = $usuario->id;
      $produto->save();

      DB::commit();

      $ret->id = $produto->id;
      $ret->data = $produto->toObject(false);
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

      $produto = Produto::find($find);
      if (!$produto) throw new Exception("Cidade não foi encontrada");

      // $regiaocount = $cidade->regioes->count();
      // if ($regiaocount>0)
      //     throw new Exception($regiaocount==1 ? 'Existe uma região associada' :  "Existem " . $regiaocount . " regiões associadas");

    } catch (\Throwable $th) {
      $ret->msg = $th->getMessage();
      return $ret->toJson();
    }


    try {
      DB::beginTransaction();

      $del = $produto->delete();

      DB::commit();

      $ret->msg = 'Produto ' . $produto->nome . ' foi excluído!';
      $ret->ok = true;
    } catch (\Throwable $th) {
      DB::rollBack();
      $ret->msg = $th->getMessage();
    }

    return $ret->toJson();
  }
}
