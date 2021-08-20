<?php

namespace App\Http\Controllers\API\V1\mobile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Motorista;
use App\Models\MotoristaTokens;

use App\Http\Controllers\RetApiController;
use Exception;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class MotoristaController extends Controller
{
    public function auth(Request $request)
    {

      $ret = new RetApiController;
      try {
        $dispositivo = session('dispositivo');
        if(!$dispositivo)
          throw new Exception('Nenhum dispositivo autenticado');

        $username = ($request->username ? $request->username : '');
        $password = $request->password ? $request->password : '';
        if($username == '' || $password=='')
          throw new Exception('Parametros de autenticação do motorista não foi encontrado');


        $motorista = Motorista::where('username', $username)->first();
        if(!$motorista)
         throw new Exception('Motorista não encontrado!');

        if($motorista->pwd == '')
         throw new Exception('Nenhuma senha cadastrada. Entre em contato com o administrador do sistema.');

        if($motorista->pwd !== $password)
          throw new Exception('Usuário e senha não confere');

      } catch (\Throwable $th) {
        $ret->msg = $th->getMessage();
        return $ret->toJson();
      }


      try {

        DB::beginTransaction();


       MotoristaTokens::where('uuid', $dispositivo->uuid)
                                 ->where('username', $motorista->username)
                                 ->delete();
        $token = new MotoristaTokens;
        $token->uuid = $dispositivo->uuid;
        $token->username = $motorista->username;
        $token->uuid = $dispositivo->uuid;
        $token->ip = \Request::ip();
        $token->expire_at = Carbon::now()->addYears();
        $token->token = md5($token->uuid . $token->expire_at->format('Ymdhis') . Carbon::now()->format('Ymdhis'). rand() . 'Conect@');
        $token->save();

        DB::commit();

        $m = [
          'id' => $motorista->id,
          'nome' => $motorista->nome,
          'username' => $motorista->username,
          'apelido' => $motorista->apelido
        ];
        $ret->data = ['useraccesscode' => $token->token,
                      'tokenexpire_at' => $token->expire_at->format('Y-m-d h:i:s'),
                      'motorista' => $m
                    ];
        $ret->ok = true;

      } catch (\Throwable $th) {
        DB::rollBack();
        $ret->msg = $th->getMessage();
      }
      return $ret->toJson();
    }
}
