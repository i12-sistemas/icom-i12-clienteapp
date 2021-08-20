<?php

namespace App\Http\Controllers\api\v1\painelcliente;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Exception;
use Illuminate\Support\Facades\DB;
use Validator;
use Carbon\Carbon;
use Illuminate\Validation\Rule;

use App\Http\Controllers\RetApiController;
use App\Models\ClienteUsuario;

class ClienteUsuarioController extends Controller
{

    public function meuperfil(Request $request)
    {
      $ret = new RetApiController;
      try {

        $usuario = session('usuario');
        $auth = session('auth');
        if (!$usuario) throw new Exception("Nenhum usuário identificado");
        if (!($usuario->id>0)) throw new Exception("Nenhum usuário identificado");

        $ret->data = $usuario->toObjectPainel($auth);
        $ret->ok = true;

      } catch (\Throwable $th) {
        $ret->msg = $th->getMessage();
      }
      return $ret->toJson();
    }


    public function savemeuperfil(Request $request)
    {
      $ret = new RetApiController;
      try {

        $usuario = session('usuario');
        $auth = session('auth');
        if (!$usuario) throw new Exception("Nenhum usuário identificado");
        if (!($usuario->id>0)) throw new Exception("Nenhum usuário identificado");

        $rules = [
            'celular' => ['string', 'max:20'],
            'nome' => ['required', 'min:1', 'max:60'],
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

        $dataset = ClienteUsuario::find($usuario->id);
        if (!$dataset) throw new Exception("Usuário não foi encontrada");

        if ($request->has('celular')) {
            $celular = cleanDocMask(trim(utf8_decode($request->celular)));
            $check = ClienteUsuario::where('celular', '=', $celular)
                            ->whereRaw('id != ?', [$dataset->id])
                            ->first();
            if ($check) throw new Exception('O celular informado esta sendo usado por outro usuário');
        }




      } catch (\Throwable $th) {
          $ret->msg = $th->getMessage();
          return $ret->toJson();
      }

      try {
          DB::beginTransaction();


          $dataset->nome = $request->nome;
          if ($request->has('celular')) $dataset->celular = $celular;
          $dataset->save();

          DB::commit();

          $ret->data = $dataset->toObjectPainel($auth);
          $ret->ok = true;
      } catch (\Throwable $th) {
        DB::rollBack();
        $ret->msg = $th->getMessage();
      }

      return $ret->toJson();
    }


}
