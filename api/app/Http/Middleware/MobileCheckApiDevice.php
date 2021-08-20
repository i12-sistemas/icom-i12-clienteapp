<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use App\Models\Dispositivo;

class MobileCheckApiDevice
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        try {
            $uuid = ($request->header('uuid') ? $request->header('uuid') : '');
            $token = $request->header('token') ? $request->header('token') : '';
            $accesscode = ($request->header('accesscode') ? $request->header('accesscode') : '');
            if($uuid == '' || $token=='' || $accesscode=='')
              throw new Exception('Parametros de validação não foi encontrado');

            $dispositivo = Dispositivo::where('uuid', $uuid)
                                      ->first();

            if(!$dispositivo) throw new Exception('Dispositivo não encontrado!');

            if($dispositivo->token !== $token)
              throw new Exception('Token expirado. Refaça login do dispositivo');

            if($dispositivo->accesscode !== $accesscode)
              throw new Exception('Token/AccessCode expirado. Refaça login do dispositivo');

            if(!$dispositivo->status === 0) throw new Exception('Dispositivo bloqueado - Pendente liberação');
            if(!$dispositivo->status === 2) throw new Exception('Dispositivo bloqueado - Acesso revogado');
            if(!$dispositivo->liberado) throw new Exception('Dispositivo bloqueado!');

            session(['dispositivo' => $dispositivo]);
            if(session('dispositivo') == null) {
              throw new Exception('Nenhum dispositivo autenticado');
            }

          } catch (\Throwable $th) {
            return response()->json('Dispositivo nao autorizado: ' . $th->getMessage(), 401);
          }
          return $next($request);
    }
}
