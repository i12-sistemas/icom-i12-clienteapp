<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Exception;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\RetApiController;


class ReceitaWSController extends Controller
{
    public function find(Request $request, $cnpj)
    {
      $ret = new RetApiController;
      try {

        $response = Http::get('https://receitaws.com.br/v1/cnpj/' . $cnpj);
        if ($response->successful()) {
            $data = $response->json();
            if ($data['status'] === 'ERROR') {
                throw new Exception($data['message']);
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
