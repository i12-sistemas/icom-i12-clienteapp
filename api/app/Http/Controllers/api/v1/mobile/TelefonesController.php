<?php

namespace App\Http\Controllers\API\V1\mobile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Telefones;
use App\Http\Controllers\RetApiController;
use Exception;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TelefonesController extends Controller
{
    public function listall() {
        $ret = new RetApiController;
        try {
            $dispositivo = session('dispositivo');
            if(!$dispositivo) throw new Exception('Nenhum dispositivo autenticado');

            $motorista = session('motorista');
            if(!$motorista) throw new Exception('Nenhum motorista autenticado');


            $telefones = Telefones::orderBy('nordem')->get();
            $ret->ok = true;
            $ret->data = $telefones;
        } catch (\Throwable $th) {
          $ret->msg = $th->getMessage();
        }
        return $ret->toJson();
      }

}
