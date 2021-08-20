<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use Illuminate\Http\Request;
use App\Models\UsuarioTokens;
use App\Models\Usuario;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class AuthApiUsuario
{
    public function handle($request, Closure $next)
    {

      try {
        $username = $request->header('x-auth-username') ? $request->header('x-auth-username') : '';
        $uuid = $request->header('x-auth-uuid') ? $request->header('x-auth-uuid') : '';
        $token = ($request->header('x-auth-token') ? $request->header('x-auth-token') : '');
        $accesscode = $request->header('x-auth-accesscode') ? $request->header('x-auth-accesscode') : '';

        if ($username == '' || $uuid=='' || $token=='' || $accesscode=='')
          throw new Exception('Parametros inválidos');

        $sessiontoken = UsuarioTokens::where('token', '=', $token)->where('accesscode', '=', $accesscode)->where('uuid', '=', $uuid)->where('username', '=', $username)->first();
        if(!$sessiontoken) throw new Exception('Sessão inválida');
        if($sessiontoken->expire_at < Carbon::Now()) throw new Exception('Sessão expirada');

        $usuario = $sessiontoken->usuario;
        if(!$usuario) throw new Exception('Sessão incompleta - sem usuário identificado');
        if($usuario->login !== $username) throw new Exception('Sessão inválida para o usuário informado');
        if($usuario->ativo !== 1) throw new Exception('Sessão inválida - Usuário inativo');


        $hashtoken = $uuid . $sessiontoken->expire_at->format('Ymdhis') . $username . $sessiontoken->ip;
        if (!\Hash::check($hashtoken, $token)) throw new Exception('Hash token inválido');

        $hashtoken = $uuid . $sessiontoken->expire_at->format('Ymdhis') . $username . $sessiontoken->ip;
        if (!\Hash::check($hashtoken, $accesscode)) throw new Exception('Hash accesscode inválido');

        session(['usuario' => $usuario]);
        session(['token' => $sessiontoken]);
      } catch (\Throwable $th) {
        return response()->json('Acesso negado: ' . $th->getMessage(), 401);
      }
      $end = microtime(TRUE);
      return $next($request);
    }
}
