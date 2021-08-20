<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Exception;
use Illuminate\Support\Facades\DB;
use Validator;
use Carbon\Carbon;
use Illuminate\Validation\Rule;

use App\Http\Controllers\RetApiController;

use App\Models\Config;

class ConfiguracoesController extends Controller
{
    public function list(Request $request)
    {

      $ret = new RetApiController;
      try {
        $idlist = null;
        if (isset($request->idlist)) {
            $idlist = explode(",", $request->idlist);
            if (!is_array($idlist)) $idlist[] = $idlist;
            $idlist = count($idlist) > 0 ? $idlist : null;
        }

        $rows = Config::whereIn('id', $idlist)->get();
        $dados=[];
        foreach ($rows as $row) {
            $dados[] = $row->export();
        }
        $ret->data = $dados;
        $ret->collection = $rows;
        $ret->ok = true;
      } catch (\Throwable $th) {
        $ret->msg = $th->getMessage();
      }
      return $ret->toJson();
    }

    public function save(Request $request)
    {
      $ret = new RetApiController;
      try {

        $usuario = session('usuario');
        if (!$usuario) throw new Exception('Nenhum usuário autenticado');

        $configuracoes = isset($request->configuracoes) ? $request->configuracoes : null;
        if (!$configuracoes) throw new Exception("Nenhum parametro de configuração encontrado!");

      } catch (\Throwable $th) {
        $ret->msg = $th->getMessage();
        return $ret->toJson();
      }

      try {
        DB::beginTransaction();

        foreach ($configuracoes as $key => $item) {

            $config = Config::where('id', '=', $item['id'])->where('tipo', '=', $item['tipo'])->first();
            if (!$config) {
                $config = new Config();
                $config->id = $item['id'];
                $config->tipo = $item['tipo'];
            }

            if ($config->tipo === 'json') {
                $config->valor = null;
                $config->texto = json_encode($item['valor']);
            } else if ($config->tipo === 'mediumtext') {
                $config->valor = null;
                $config->texto = $item['valor'];
            } else {
                $config->valor = $item['valor'];
            }

            $config->updated_usuarioid = $usuario->id;
            $config->updated_at = Carbon::now();
            $config->save();
        }


        DB::commit();

        $ret->ok = true;

      } catch (\Throwable $th) {
        DB::rollBack();
        $ret->msg = $th->getMessage();
      }


      return $ret->toJson();
    }

}
