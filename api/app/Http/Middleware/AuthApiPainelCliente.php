<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use Illuminate\Http\Request;
use App\Models\ClienteUsuarioTokens;
use App\Models\ClienteUsuario;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class AuthApiPainelCliente
{
    public function handle($request, Closure $next)
    {

      try {
        $username = $request->header('x-auth-username') ? $request->header('x-auth-username') : '';
        // $uuid = $request->header('x-auth-uuid') ? $request->header('x-auth-uuid') : '';
        $usernametype = $request->header('x-auth-usernametype') ? $request->header('x-auth-usernametype') : '';
        $token = ($request->header('x-auth-token') ? $request->header('x-auth-token') : '');
        $accesscode = $request->header('x-auth-accesscode') ? $request->header('x-auth-accesscode') : '';
        if ($username == '' || $usernametype=='' || $token=='' || $accesscode=='')
          throw new Exception('Parametros inválidos');

          if (!(($usernametype === 'celular' || $usernametype==='email'))) throw new Exception('Parametros "usernametype" inválido');

        $sessiontoken = ClienteUsuarioTokens::where('token', '=', $token)->where('accesscode', '=', $accesscode)->where($usernametype, '=', $username)->first();
        if(!$sessiontoken) throw new Exception('Sessão inválida');
        if($sessiontoken->expire_at < Carbon::Now()) throw new Exception('Sessão expirada');

        $usuario = $sessiontoken->clienteusuario;
        if(!$usuario) throw new Exception('Sessão incompleta - sem usuário identificado');
        if ($usernametype === 'celular') {
            if($usuario->celular !== $username) throw new Exception('Sessão inválida para o usuário informado');
        } else if ($usernametype === 'email') {
            if($usuario->email !== $username) throw new Exception('Sessão inválida para o usuário informado');
        } else {
            throw new Exception('Sessão inválida para o usuário informado (-3)');
        }

        if($usuario->ativo !== 1) throw new Exception('Sessão inválida - Usuário inativo');

        $hashtoken = $sessiontoken->clienteusuarioid . '***' . $sessiontoken->expire_at->format('Ymdhis') . $username . $sessiontoken->ip;
        if (!\Hash::check($hashtoken, $token)) throw new Exception('Hash token inválido');

        $hashtoken = 'check'. $sessiontoken->expire_at->format('Ymdhis') . $sessiontoken->username . $sessiontoken->ip;
        if (!\Hash::check($hashtoken, $accesscode)) throw new Exception('Hash accesscode inválido');

        session(['usuario' => $usuario]);
        session(['auth' => ['username' => $username, 'usernametype' => $usernametype]]);
        session(['token' => $sessiontoken]);
      } catch (\Throwable $th) {
        return response()->json('Acesso negado: ' . $th->getMessage(), 401);
      }
      $end = microtime(TRUE);
      return $next($request);
    }

}
