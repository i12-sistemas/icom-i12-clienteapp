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

use App\Models\Usuario;
use App\Models\UsuarioTokens;
use App\Models\UsuarioResetPwdTokens;

use App\Jobs\Usuario\ResetPwdRequestJob;
use App\Jobs\Usuario\ResetPwdChangedJob;

class UsuarioAuthController extends Controller
{

    public function auth(Request $request)
    {

      $ret = new RetApiController;
      try {
        $uuid = $request->header('x-auth-uuid') ? $request->header('x-auth-uuid') : '';
        // if (!$uuid) throw new Exception('UUID inválida');
        // if (strlen($uuid)<36) throw new Exception('UUID inválida');


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


        UsuarioTokens::where('uuid', $uuid)
                        ->where('username', $usuario->login)
                        ->delete();
        $token = new UsuarioTokens;
        $token->uuid = $uuid;
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

    public function resetpwd_request(Request $request)
    {

      $ret = new RetApiController;
      try {
        $uuid = $request->header('x-auth-uuid') ? $request->header('x-auth-uuid') : '';
        // if (!$uuid) throw new Exception('UUID inválida');
        // if (strlen($uuid)<36) throw new Exception('UUID inválida');


        header('Cache-Control: no-cache, must-revalidate, max-age=0');
        $has_supplied_credentials = !(empty($_SERVER['PHP_AUTH_USER']) && empty($_SERVER['PHP_AUTH_PW']));
        if (!$has_supplied_credentials)
            throw new Exception('Credencial inválida');

        $username = $_SERVER['PHP_AUTH_USER'];
        $email = $_SERVER['PHP_AUTH_PW'];
        if($username == '' || $email=='')
          throw new Exception('Credencial inválida');


        $usuario = Usuario::where('login', $username)->where('email', $email)->first();
        if(!$usuario)
         throw new Exception('Nenhum usuário não encontrado com os dados informados');

      } catch (\Throwable $th) {
        $ret->msg = $th->getMessage();
        return $ret->toJson();
      }


      try {
        DB::beginTransaction();


        UsuarioResetPwdTokens::where('usuarioid', $usuario->id)
                            ->where('processado', 0)
                            ->update([
                                'updated_at' => Carbon::now(),
                                'processado' => 2
                            ]);

        $token = new UsuarioResetPwdTokens;
        $token->uuid = $uuid;
        $token->username = $usuario->login;
        $token->email = $usuario->email;
        $token->usuarioid = $usuario->id;
        $token->ip = \Request::ip();
        $token->processado = 0;
        $token->expire_at = Carbon::now()->addHours(1);
        $token->codenumber = rand(10000000 , 99999999);
        $token->token = bcrypt($token->uuid . $token->expire_at->format('Ymdhis') . $token->username . $token->ip . $token->codenumber . Carbon::now());
        $token->save();

        DB::commit();

        $this->dispatch(new ResetPwdRequestJob($token));

        $ret->msg = 'E-mail será enviado para ' . $usuario->email;
        $ret->ok = true;

      } catch (\Throwable $th) {
        DB::rollBack();
        $ret->msg = $th->getMessage();
      }
      return $ret->toJson();
    }

    public function resetpwd_checkcode(Request $request)
    {

      $ret = new RetApiController;
      try {
        // $uuid = $request->header('x-auth-uuid') ? $request->header('x-auth-uuid') : '';
        // if (!$uuid) throw new Exception('UUID inválida');
        // if (strlen($uuid)<36) throw new Exception('UUID inválida');


        $username = isset($request->username) ? $request->username : null;
        $email = isset($request->email) ? $request->email : null;
        $codenumber = isset($request->codenumber) ? intVal($request->codenumber) : 0;

        if($username == '' || $email=='')
          throw new Exception('Credencial inválida');

        if(!($codenumber > 0 ))
          throw new Exception('Código inválido');


        $token = UsuarioResetPwdTokens::where('username', $username)
                                        ->where('email', $email)
                                        ->where('codenumber', $codenumber)
                                        ->where('processado', 0)
                                        ->first();
        if(!$token)
         throw new Exception('Código não foi encontrado ou já foi processado anteriormente');

        if($token->expire_at < Carbon::now())
         throw new Exception('Código expirado.');


         $ret->data = [
            'codenumber' => $token->codenumber,
            'token' => $token->token,
            'username' => $token->username,
            'email' => $token->email
         ];
         $ret->ok = true;


      } catch (\Throwable $th) {
        $ret->msg = $th->getMessage();
        return $ret->toJson();
      }

      return $ret->toJson();
    }


    public function resetpwd_changepwd(Request $request)
    {

      $ret = new RetApiController;
      try {
        // $uuid = $request->header('x-auth-uuid') ? $request->header('x-auth-uuid') : '';
        // if (!$uuid) throw new Exception('UUID inválida');
        // if (strlen($uuid)<36) throw new Exception('UUID inválida');


        $username = isset($request->username) ? $request->username : null;
        $email = isset($request->email) ? $request->email : null;
        $codenumber = isset($request->codenumber) ? intVal($request->codenumber) : 0;
        $token = isset($request->token) ? intVal($request->token) : '';
        $pwd = isset($request->pwd) ? $request->pwd : '';

        if($username == '' || $email=='')
          throw new Exception('Credencial inválida');

        if($pwd == '')
          throw new Exception('Senha inválida');

        if(!($codenumber > 0 ))
          throw new Exception('Código inválido');


        $token = UsuarioResetPwdTokens::where('username', $username)
                                        ->where('email', $email)
                                        ->where('codenumber', $codenumber)
                                        ->where('token', $token)
                                        ->where('processado', 0)
                                        ->first();
        if(!$token)
         throw new Exception('Token não foi encontrado ou já foi processado anteriormente');

        if($token->expire_at < Carbon::now())
         throw new Exception('Token expirado');

      } catch (\Throwable $th) {
        $ret->msg = $th->getMessage();
        return $ret->toJson();
      }


      try {
        DB::beginTransaction();

        $usuario = $token->usuario;
        $usuario->senha = bcrypt($pwd);
        $usuario->save();

        $token->processado = 1;
        $token->save();

        UsuarioResetPwdTokens::where('usuarioid', $usuario->id)
                            ->where('processado', 0)
                            ->update([
                                'updated_at' => Carbon::now(),
                                'processado' => 2
                            ]);

        DB::commit();

        $ret->ok = true;

        $this->dispatch(new ResetPwdChangedJob($token));

      } catch (\Throwable $th) {
        DB::rollBack();
        $ret->msg = $th->getMessage();
      }
      return $ret->toJson();
    }

    public function resetpwd_revoke(Request $request)
    {

      $ret = new RetApiController;
      try {
        // $uuid = $request->header('x-auth-uuid') ? $request->header('x-auth-uuid') : '';
        // if (!$uuid) throw new Exception('UUID inválida');
        // if (strlen($uuid)<36) throw new Exception('UUID inválida');


        $username = isset($request->username) ? $request->username : null;
        $email = isset($request->email) ? $request->email : null;
        $codenumber = isset($request->codenumber) ? intVal($request->codenumber) : 0;
        $token = isset($request->token) ? intVal($request->token) : '';

        if($username == '' || $email=='')
          throw new Exception('Credencial inválida');

        if(!($codenumber > 0 ))
          throw new Exception('Código inválido');


        $token = UsuarioResetPwdTokens::where('username', $username)
                                        ->where('email', $email)
                                        ->where('codenumber', $codenumber)
                                        ->where('token', $token)
                                        ->where('processado', 0)
                                        ->first();
        if(!$token)
         throw new Exception('Token não foi encontrado ou já foi processado anteriormente');

      } catch (\Throwable $th) {
        $ret->msg = $th->getMessage();
        return $ret->toJson();
      }


      try {
        DB::beginTransaction();

        $token->processado = 2;
        $token->save();

        UsuarioResetPwdTokens::where('usuarioid', $token->usuario->id)
                            ->where('processado', 0)
                            ->update([
                                'updated_at' => Carbon::now(),
                                'processado' => 2
                            ]);

        DB::commit();

        $ret->ok = true;

      } catch (\Throwable $th) {
        DB::rollBack();
        $ret->msg = $th->getMessage();
      }
      return $ret->toJson();
    }
}
