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

use App\Models\Unidade;

class UnidadeController extends Controller
{
    public function list(Request $request)
    {
      $ret = new RetApiController;
      try {
        $find = isset($request->find) ? utf8_decode($request->find) : null;
        $perpage = isset($request->perpage) ? $request->perpage : 25;
        $unidades = Unidade::when($find, function ($query, $find) {
                      return $query->Where('razaosocial', 'like', '%'.$find.'%')
                            ->orWhere('fantasia', 'like','%'.$find.'%')
                            ->orWhere('cnpj', 'like', '%'. cleanDocMask($find).'%')
                            ->orWhere('fone', 'like', '%'.$find.'%')
                            ->orWhere('logradouro', 'like', '%'.$find.'%')
                            ->orWhere('endereco', 'like', '%'.$find.'%')
                            ->orWhere('bairro', 'like', '%'.$find.'%')
                            ->orWhere('complemento', 'like', '%'.$find.'%')
                            ->orWhere('ie', 'like', '%'. cleanDocMask($find) .'%');
                    })
                    ->orderBy('razaosocial')
                    ->orderby('fantasia')
                    ->paginate($perpage);

        $dados = [];
        foreach ($unidades as $unidade) {
            $dados[] = $unidade->toObject();
        }
        $ret->data = $dados;
        $ret->collection = $unidades;
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

        $unidade = Unidade::find($find);
        if (!$unidade) throw new Exception("Unidade não foi encontrada");

        $ret->data = $unidade->toObject(True);
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
            'cnpj' => [
                    'string',
                    'size:14',
                    'required',
                    Rule::unique('unidade')->ignore(isset($request->id) ? intVal($request->id) : 0),
                ],
            'cidadeid' => ['required', 'integer'],
            'razaosocial' => ['required', 'max:255'],
            'fantasia' => ['required', 'max:255'],
            'fone' => ['max:20'],
            'ie' => ['max:14'],
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
            $unidade = Unidade::find($id);
            if (!$unidade) throw new Exception("Unidade não foi encontrada");
        }

      } catch (\Throwable $th) {
        $ret->msg = $th->getMessage();
        return $ret->toJson();
      }

      try {
        DB::beginTransaction();


        if ($action=='add') {
            $unidade = new Unidade();
            $unidade->created_usuarioid = $usuario->id;
        }

        if (isset($request->razaosocial)) $unidade->razaosocial = $request->razaosocial;
        if (isset($request->fantasia)) $unidade->fantasia = $request->fantasia;
        if (isset($request->cnpj)) $unidade->cnpj = $request->cnpj;
        if (isset($request->ie)) $unidade->ie = $request->ie;
        if (isset($request->fone)) $unidade->fone = $request->fone;
        if (isset($request->ativo)) $unidade->ativo = $request->ativo;

        if (isset($request->logradouro)) $unidade->logradouro = $request->logradouro;
        if (isset($request->endereco)) $unidade->endereco = $request->endereco;
        if (isset($request->numero)) $unidade->numero = $request->numero;
        if (isset($request->bairro)) $unidade->bairro = $request->bairro;
        if (isset($request->cep)) $unidade->cep = $request->cep;
        if (isset($request->complemento)) $unidade->complemento = $request->complemento;
        if (isset($request->cidadeid)) $unidade->cidadeid = $request->cidadeid;

        $unidade->updated_usuarioid = $usuario->id;
        $unidade->save();

        DB::commit();

        $ret->id = $unidade->id;
        $ret->data = $unidade->toObject(false);
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

        $unidade = Unidade::find($find);
        if (!$unidade) throw new Exception("Unidade não foi encontrada");

        // $regiaocount = $cidade->regioes->count();
        // if ($regiaocount>0)
        //     throw new Exception($regiaocount==1 ? 'Existe uma região associada' :  "Existem " . $regiaocount . " regiões associadas");

      } catch (\Throwable $th) {
        $ret->msg = $th->getMessage();
        return $ret->toJson();
      }


      try {
        DB::beginTransaction();

        $del = $unidade->delete();

        DB::commit();

        $ret->msg = 'Unidade ' . $unidade->fantasia . ' foi excluída!';
        $ret->ok = true;
      } catch (\Throwable $th) {
        DB::rollBack();
        $ret->msg = $th->getMessage();
      }

      return $ret->toJson();
    }
}
