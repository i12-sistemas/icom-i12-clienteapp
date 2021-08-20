<?php

namespace App\Http\Controllers\api\v1\mobile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Exception;
use Illuminate\Support\Facades\DB;
use Validator;
use Carbon\Carbon;
use Illuminate\Validation\Rule;

use App\Http\Controllers\RetApiController;

use App\Models\Usuario;
use App\Models\UsuarioTokens;
use App\Models\UsuarioResetPwdTokens;

use App\Jobs\Usuario\ResetPwdRequestJob;
use App\Jobs\Usuario\ResetPwdChangedJob;


class UsuarioAdminAuth extends Controller
{
    public function auth(Request $request)
    {

      $ret = new RetApiController;
      try {
        $dispositivo = session('dispositivo');
        if(!$dispositivo)
          throw new Exception('Nenhum dispositivo autenticado');


        header('Cache-Control: no-cache, must-revalidate, max-age=0');
        $has_supplied_credentials = !(empty($_SERVER['PHP_AUTH_USER']) && empty($_SERVER['PHP_AUTH_PW']));
        if (!$has_supplied_credentials)
            throw new Exception('Credencial inválida');

        $username = $_SERVER['PHP_AUTH_USER'];
        $password = $_SERVER['PHP_AUTH_PW'];
        if($username == '' || $password=='')
          throw new Exception('Credencial inválida');


        $usuario = Usuario::where('login', $username)->first();
        if(!$usuario)
         throw new Exception('Usuário não encontrado');

        if($usuario->senha == '')
         throw new Exception('Nenhuma senha cadastrada. Entre em contato com o administrador do sistema.');

         if (!\Hash::check($password , $usuario->senha))
            throw new Exception('Senha inválida.');

      } catch (\Throwable $th) {
        $ret->msg = $th->getMessage();
        return $ret->toJson();
      }


      try {
        DB::beginTransaction();


        UsuarioTokens::where('uuid', $dispositivo->uuid)
                        ->where('username', $usuario->login)
                        ->delete();
        $token = new UsuarioTokens;
        $token->uuid = $dispositivo->uuid;
        $token->username = $usuario->login;
        $token->ip = \Request::ip();
        $token->expire_at = Carbon::now()->addDays(7);
        $token->token = bcrypt($token->uuid . $token->expire_at->format('Ymdhis') . $token->username . $token->ip);
        $token->save();

        DB::commit();

        $user = [
          'hashid' => md5($usuario->id . $usuario->login),
          'nome' => $usuario->nome,
          'username' => $usuario->login,
        ];
        $ret->data = [
                'usertoken' => $token->token,
                'usertokenexpire_at' => $token->expire_at->format('Y-m-d h:i:s'),
                'user' => $user
                ];
        $ret->ok = true;

      } catch (\Throwable $th) {
        DB::rollBack();
        $ret->msg = $th->getMessage();
      }
      return $ret->toJson();
    }

    public function checklogin(Request $request)
    {

      $ret = new RetApiController;
      try {
        // $dispositivo = session('dispositivo');
        // if(!$dispositivo)
        //   throw new Exception('Nenhum dispositivo autenticado');

        header('Cache-Control: no-cache, must-revalidate, max-age=0');
        $has_supplied_credentials = !(empty($_SERVER['PHP_AUTH_USER']) && empty($_SERVER['PHP_AUTH_PW']));
        if (!$has_supplied_credentials)
            throw new Exception('Credencial inválida');

        $uuid = $request->header('x-auth-uuid') ? $request->header('x-auth-uuid') : '';
        // if (!$uuid) throw new Exception('UUID inválida');
        // if (strlen($uuid)<36) throw new Exception('UUID inválida');

        $username = $_SERVER['PHP_AUTH_USER'];
        $password = $_SERVER['PHP_AUTH_PW'];

        $token = UsuarioTokens::where('username', $username)->where('token', $password)->where('uuid', $uuid)->first();
        if(!$token) throw new Exception('Token inválido');
        if ($token->expire_at < Carbon::now()) throw new Exception('Token expirado');

        $usuario = Usuario::where('login', $username)->first();
        if(!$usuario) throw new Exception('Usuário não encontrado');
        if(!$usuario->ativo) throw new Exception('Usuário inativo');

      } catch (\Throwable $th) {
        abort(403, 'Acesso negado: ' . $th->getMessage());
      }

      try {
        if (!$token->accesscode) {
            DB::beginTransaction();

            $token->accesscode = bcrypt($token->uuid . $token->expire_at->format('Ymdhis') . $token->username . $token->ip);
            $token->save();

            DB::commit();
        }

        $user = $usuario->toCompleteArray();
        $user['permissoes'] = $usuario->permissoesLiberadas();

        $ret->data = [
            'usertoken' => $token->token,
            'accesscode' => $token->accesscode,
            'usertokenexpire_at' => $token->expire_at->format('Y-m-d h:i:s'),
            'user' => $user
            ];
        $ret->ok = true;

      } catch (\Throwable $th) {
        DB::rollBack();
        $ret->msg = $th->getMessage();
      }
      return $ret->toJson();
    }
}
