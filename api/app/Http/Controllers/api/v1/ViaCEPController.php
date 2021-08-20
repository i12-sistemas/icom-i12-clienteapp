<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Exception;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\RetApiController;

class ViaCEPController extends Controller
{
    public function find(Request $request, $cep)
    {
      $ret = new RetApiController;
      try {
        $url = 'https://viacep.com.br/ws/' . $cep . '/json/';
        $response = Http::get($url);
        if ($response->successful()) {
            $data = $response->json();
            if (isset($data['erro'])) {
                throw new Exception('Erro ao consultar CEP. Verifique se é um CEP válido.');
            }
            $ret->data = $data;
            $ret->ok = true;
        } else {
            $response->throw();
        }
      } catch (\Throwable $th) {
        $ret->msg = $th->getMessage();
      }
      return $ret->toJson();
    }
}
