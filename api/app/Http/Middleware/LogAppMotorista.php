<?php

namespace App\Http\Middleware;

use Closure;

use Exception;
use Carbon\Carbon;
use App\Models\AppMotoristaLog;
use Illuminate\Support\Facades\DB;

class LogAppMotorista
{

    public function handle($request, Closure $next)
    {
        $uuid = null;
        $dispositivo = session('dispositivo');
        if ($dispositivo) $uuid = $dispositivo->uuid;
        if (!$uuid) $uuid = isset($request->uuid) ? $request->uuid : '';
        if (!$uuid) $uuid = $request->header('uuid') ? $request->header('uuid') : '';
        $motoristaid = null;
        $motorista = session('motorista');
        if ($motorista) $motoristaid = $motorista->id;
        if ($uuid) {
            try {
                DB::beginTransaction();

                $log = new AppMotoristaLog();
                $log->uuid = $uuid;
                if ($motoristaid > 0) $log->motoristaid = $motoristaid;
                $log->created_at = Carbon::now();
                $log->ip = \Request::ip();
                $log->host = $request->getHost();
                $log->uri = $request->getPathInfo();
                $log->request = json_encode($request->all());
                $log->save();

                DB::commit();
            } catch (\Throwable $th) {
                DB::rollBack();
                \Log::error('Error registrar logion do appmotorista :: ' . $th->getMessage());
            }
        }
        return $next($request);
    }
}
