<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use App\Models\Motorista;
use App\Models\MotoristaTokens;
use Carbon\Carbon;

class MobileCheckApiMotorista
{
    public function handle($request, Closure $next)
    {
        try {
            $dispositivo = session('dispositivo');
            if(!$dispositivo)
              throw new Exception('Nenhum dispositivo autenticado');

            $user = ($request->getUser() ? $request->getUser() : '');
            $password = $request->getPassword() ? $request->getPassword() : '';

            // throw new Exception($user . ' - ' . $password);
            if($user == '' || $password=='')
              throw new Exception('Parametros de autenticação do motorista não foi encontrado');

            $motorista = Motorista::where('username', $user)
                                      ->first();

            if(!$motorista) throw new Exception('Motorista não encontrado!');

            $token = MotoristaTokens::where('username', $motorista->username)
                                      ->where('token', $password)
                                      ->where('uuid', $dispositivo->uuid)
                                      ->first();
            if(!$token)
              throw new Exception('Acesso expirado. Refaça login do motorista');

            if($token->expire_at < Carbon::now())
              throw new Exception('Token expirado. Refaça login do motorista');

            session(['motorista' => $motorista]);
            if(session('motorista') == null) {
              throw new Exception('Nenhum motorista autenticado');
            }
          } catch (\Throwable $th) {
            return response()->json('Acesso não autorizado: ' . $th->getMessage(), 401);
          }
          return $next($request);

    }
}
