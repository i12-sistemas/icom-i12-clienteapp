<?php

namespace App\Http\Controllers\API\V1\mobile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Cliente;
use App\Http\Controllers\RetApiController;
use Exception;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ClientesController extends Controller
{
    public function listall(Request $request) {
        $ret = new RetApiController;
        try {
            $dispositivo = session('dispositivo');
            if(!$dispositivo) throw new Exception('Nenhum dispositivo autenticado');

            $motorista = session('motorista');
            if(!$motorista) throw new Exception('Nenhum motorista autenticado');


            $page = isset($request->page) ? intval($request->page) : 1;
            if(!($page>0)) $page = 1;

            $pagesize = isset($request->pagesize) ? intval($request->pagesize) : 1000;
            if(!($pagesize>0)) $pagesize = 1000;

            $lastsyncupdate = isset($request->lastsyncupdate) ? $request->lastsyncupdate : null;
            if($lastsyncupdate) {
            $lastsyncupdate = Carbon::parse($lastsyncupdate);
            }

            $clientes = Cliente::whereRaw('if(? is null, true, updated_at >= ?)', [$lastsyncupdate,$lastsyncupdate])
                                ->paginate($pagesize);
            // $ret->data = $clientes->get();
            $ret->collection = $clientes;
            $ret->data = $clientes;

            $ret->ok = true;
            // $ret->data = $data;
        } catch (\Throwable $th) {
          $ret->msg = $th->getMessage();
        }
        return $ret->toJson();
      }
}
