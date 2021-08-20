<?php

namespace App\Http\Middleware;

use Closure;

class CorsMiddleware
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
        $headers =  [
            // '*',
            'access-control-allow-headers',
            'access-control-allow-methods',
            'access-control-allow-origin',
            'authorization',
            'Access-Control-Request-Headers',
            'Access-Control-Allow-Methods',
            'Access-Control-Allow-Origin',
            'Authorization',
            'Content-Type',
            'content-type',
            'Accept',
            'x-auth-uuid',
            'x-auth-accesscode',
            'x-auth-token',
            'x-auth-username',
            'x-auth-usernametype',
            'accesscode',
            'uuid',
            'token'
        ];

        $secure_connection = false;
        $protocol = $secure_connection ? 'https://' : 'http://';
        if(isset($_SERVER['HTTPS'])) {
            if ($_SERVER['HTTPS'] == "on") {
                $secure_connection = true;
            }
        }
        // $http_origin = $protocol . $request->server('HTTP_ORIGIN');
        $http_origin = $request->server('HTTP_ORIGIN');
        if ($http_origin ? $http_origin === '' : true) $http_origin = $request->server('HTTP_HOST');
        $temhttp = strpos($http_origin, 'http');
        if ($temhttp === false) $http_origin = $protocol . $http_origin;
        $origin_allowed =  [
            '*',
            'http://192.168.0.30',
            'http://conectaapi.local',
            'http://coletas.conecta.ind.br',
            'http://coletasapi.conecta.ind.br',
            'http://coletas.conecta.ind.br',
            'https://269042b9b04e.ngrok.io/',
            'http://cliente.conecta.local:8089',
            'http://cliente.conecta.local:8089/',
            'http://localhost:8080',
            'http://localhost:8080/'
        ];
        $origin = '*';
        if (in_array($http_origin , $origin_allowed)) $origin = $http_origin;
        if (in_array('*' , $origin_allowed)) $origin = '*';
        $origin = '*';
        return $next($request)
                //Acrescente as 3 linhas abaixo
                ->header('Access-Control-Allow-Origin', $origin)
                ->header('Access-Control-Allow-Methods', "GET, POST, OPTIONS, PUT, PATCH, DELETE")
                ->header('Access-Control-Allow-Headers', implode (',', $headers));
    }
}
