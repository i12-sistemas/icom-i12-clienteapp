<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Events\NotificationEvent;

use App\Http\Controllers\RetApiController;

class EchoServerController extends Controller
{
    public function teste()
  {
    $ret = new RetApiController;
    try {


        $notificacao = [
            'title' => 'teste',
            'msg' => 'teste',
            'icon' => 'check',
            'color' => 'accent',
            'url' => env('APP_URL_FRONT'),
            'urltarget' => '_blank',
            'urllabel' => 'Abrir site da i12',
        ];
        event(new NotificationEvent('teste', 'info', $notificacao));
        $ret->msg = 'TESTE EVENTE';
        $ret->ok = true;
    } catch (\Throwable $th) {
      $ret->msg = $th->getMessage();
    }
    return $ret->toJson();
  }

  public function echo()
  {
    $ret = new RetApiController;
    try {
      $ret->msg = 'Servidor online';
      $ret->ok = true;
    } catch (\Throwable $th) {
      $ret->msg = $th->getMessage();
    }
    return $ret->toJson();
  }
}
