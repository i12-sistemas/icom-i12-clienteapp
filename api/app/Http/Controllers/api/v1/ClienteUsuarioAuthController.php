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

use App\Models\ClienteUsuario;
use App\Models\ClienteUsuarioTokens;
use App\Models\ClienteUsuarioResetPwdTokens;

use App\Jobs\Cliente\UsuarioClienteResetPwdRequestJob;
// use App\Jobs\Usuario\ResetPwdChangedJob;


class ClienteUsuarioAuthController extends Controller
{

    public function auth(Request $request)
    {

      $ret = new RetApiController;
      try {
        header('Cache-Control: no-cache, must-revalidate, max-age=0');
        $has_supplied_credentials = !(empty($_SERVER['PHP_AUTH_USER']) && empty($_SERVER['PHP_AUTH_PW']));
        if (!$has_supplied_credentials)
            throw new Exception('Credencial inválida');

        $username = $_SERVER['PHP_AUTH_USER'];
        $password = $_SERVER['PHP_AUTH_PW'];
        if($username == '' || $password=='')
          throw new Exception('Credencial inválida');

        $tipologin = null;

        if (validEmail($username)) $tipologin = 'email';
        if (($tipologin === null) && (is_numeric($username))) $tipologin = 'celular';
        if (($tipologin !== 'email') && ($tipologin !== 'celular'))
            throw new Exception('Tipo de usuário não foi identificado como login por telefone ou e-mail');

        $usuario = ClienteUsuario::where($tipologin, $username)->first();
        if(!$usuario)
         throw new Exception('Usuário não encontrado');

        if($usuario->senha == '')
            throw new Exception('Nenhuma senha cadastrada. Faço o processo de alterar senha.');

         if (!\Hash::check($password , $usuario->senha))
            throw new Exception('Senha inválida.');

        $username = $usuario[$tipologin];
      } catch (\Throwable $th) {
        $ret->msg = $th->getMessage();
        return $ret->toJson();
      }


      try {
        DB::beginTransaction();


        ClienteUsuarioTokens::where($tipologin, $username)->delete();

        $token = new ClienteUsuarioTokens;
        $token->clienteusuarioid = $usuario->id;
        $token[$tipologin] = $username;
        $token->ip = \Request::ip();
        $token->expire_at = Carbon::now()->addDays(7);
        $token->token = bcrypt($token->clienteusuarioid . '***' . $token->expire_at->format('Ymdhis') . $username . $token->ip);
        $token->save();

        DB::commit();

        $user = [
          'hashid' => md5($usuario->id . $username),
          'nome' => $usuario->nome,
          'username' => $username
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
        header('Cache-Control: no-cache, must-revalidate, max-age=0');
        $has_supplied_credentials = !(empty($_SERVER['PHP_AUTH_USER']) && empty($_SERVER['PHP_AUTH_PW']));
        if (!$has_supplied_credentials)
            throw new Exception('Credencial inválida');

        // $uuid = $request->header('x-auth-uuid') ? $request->header('x-auth-uuid') : '';
        $username = $_SERVER['PHP_AUTH_USER'];
        $password = $_SERVER['PHP_AUTH_PW'];

        $tipologin = null;
        if (validEmail($username)) $tipologin = 'email';
        if (($tipologin === null) && (is_numeric($username))) $tipologin = 'celular';
        if (($tipologin !== 'email') && ($tipologin !== 'celular'))
            throw new Exception('Tipo de usuário não foi identificado como login por telefone ou e-mail');


        $token = ClienteUsuarioTokens::where($tipologin, '=', $username)->where('token', $password)->first();
        if(!$token) throw new Exception('Token inválido');
        if ($token->expire_at < Carbon::now()) throw new Exception('Token expirado');

        $usuario = $token->clienteusuario;
        if(!$usuario) throw new Exception('Usuário não encontrado');
        if(!$usuario->ativo) throw new Exception('Usuário inativo');

      } catch (\Throwable $th) {
        abort(403, 'Acesso negado: ' . $th->getMessage());
      }

      try {
        DB::beginTransaction();
        if (!$token->accesscode) {
            $token->accesscode = bcrypt('check'. $token->expire_at->format('Ymdhis') . $token->username . $token->ip);
            $token->save();
        }
        $usuario->ultimoacesso = Carbon::now();
        $usuario->save();
        DB::commit();

        $user = $usuario->toCompleteArray();
        $user['username'] = $username;

        $ret->data = [
            'usernametype' => $tipologin,
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
        if($username == '')
          throw new Exception('Credencial inválida');

        $tipologin = null;
        if (validEmail($username)) $tipologin = 'email';
        if (($tipologin === null) && (is_numeric($username))) $tipologin = 'celular';
        if (($tipologin !== 'email') && ($tipologin !== 'celular'))
            throw new Exception('Tipo de usuário não foi identificado como login por telefone ou e-mail');

        $usuario = ClienteUsuario::where($tipologin, '=', $username)->first();
        if(!$usuario)
         throw new Exception('Nenhum usuário não encontrado com os dados informados');

      } catch (\Throwable $th) {
        $ret->msg = $th->getMessage();
        return $ret->toJson();
      }


      try {
        DB::beginTransaction();


        ClienteUsuarioResetPwdTokens::where('clienteusuarioid', $usuario->id)
                            ->where('processado', 0)
                            ->update([
                                'updated_at' => Carbon::now(),
                                'processado' => 2
                            ]);

        $token = new ClienteUsuarioResetPwdTokens;
        $token->email = $usuario->email;
        if ($tipologin === 'celular') $token->celular = $username;
        $token->clienteusuarioid = $usuario->id;
        $token->ip = \Request::ip();
        $token->processado = 0;
        $token->expire_at = Carbon::now()->addHours(1);
        $token->codenumber = rand(10000000 , 99999999);
        $token->token = bcrypt($token->expire_at->format('Ymdhis') . $username . $token->ip . $token->codenumber . Carbon::now());
        $token->save();

        DB::commit();

        $this->dispatch(new UsuarioClienteResetPwdRequestJob($token));

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
        $username = isset($request->username) ? $request->username : null;
        $codenumber = isset($request->codenumber) ? intVal($request->codenumber) : 0;

        if($username == '') throw new Exception('Credencial inválida');
        if(!($codenumber > 0 )) throw new Exception('Código inválido');

        $tipologin = null;
        if (validEmail($username)) $tipologin = 'email';
        if (($tipologin === null) && (is_numeric($username))) $tipologin = 'celular';
        if (($tipologin !== 'email') && ($tipologin !== 'celular'))
            throw new Exception('Tipo de usuário não foi identificado como login por telefone ou e-mail');


        $token = ClienteUsuarioResetPwdTokens::where($tipologin, $username)
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
            'username' => $username
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
        $username = isset($request->username) ? $request->username : null;
        $codenumber = isset($request->codenumber) ? intVal($request->codenumber) : 0;
        $token = isset($request->token) ? intVal($request->token) : '';
        $pwd = isset($request->pwd) ? $request->pwd : '';

        if($username == '') throw new Exception('Credencial inválida');
        if($pwd == '') throw new Exception('Senha inválida');
        if(!($codenumber > 0 )) throw new Exception('Código inválido');

        $tipologin = null;
        if (validEmail($username)) $tipologin = 'email';
        if (($tipologin === null) && (is_numeric($username))) $tipologin = 'celular';
        if (($tipologin !== 'email') && ($tipologin !== 'celular'))
            throw new Exception('Tipo de usuário não foi identificado como login por telefone ou e-mail');



        $token = ClienteUsuarioResetPwdTokens::where($tipologin, $username)
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

        $usuario = $token->clienteusuario;
        $usuario->senha = bcrypt($pwd);
        $usuario->save();

        $token->processado = 1;
        $token->save();

        ClienteUsuarioResetPwdTokens::where('clienteusuarioid', $usuario->id)
                            ->where('processado', 0)
                            ->update([
                                'updated_at' => Carbon::now(),
                                'processado' => 2
                            ]);

        DB::commit();

        $ret->ok = true;

        // $this->dispatch(new ResetPwdChangedJob($token));

      } catch (\Throwable $th) {
        DB::rollBack();
        $ret->msg = $th->getMessage();
      }
      return $ret->toJson();
    }



}
