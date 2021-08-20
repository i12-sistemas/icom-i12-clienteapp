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

use App\Models\Dispositivo;
use App\Models\DispositivoLink;;

class DispositivoController extends Controller
{
    public function list(Request $request)
    {
      $ret = new RetApiController;
      try {
        $status = isset($request->status) ? $request->status : null;
        $find = isset($request->find) ? utf8_decode($request->find) : null;
        $perpage = isset($request->perpage) ? $request->perpage : 25;
        $ids = null;
        if (isset($request->ids)) {
            $ids = explode(",", $request->ids);
            if (!is_array($ids)) $ids[] = $ids;
            $ids = count($ids) > 0 ? $ids : null;
        }

        $dataset = Dispositivo::with('linksabertos', 'updated_usuario')

                    ->when($find, function ($query, $find) {
                      return $query->Where('descricao', 'like', '%'.$find.'%')
                            ->orWhere('uuid', 'like', '%'.$find.'%')
                            ->orWhere('version', 'like', '%'.$find.'%')
                            ->orWhere('model', 'like','%'. $find.'%')
                            ->orWhere('fabricante', 'like', '%'.$find.'%')
                            ->orWhere('platform', 'like', '%'.$find.'%');
                    })
                    ->when($request->ids, function ($query) use ($ids) {
                        return $query->whereIn('id', $ids);
                    })
                    ->when(isset($request->status) && ($status >= 0), function ($query) use ($status) {
                        return $query->where('status', '=', $status);
                    })
                    ->orderBy('descricao')
                    ->paginate($perpage);
        $dados = [];
        foreach ($dataset as $row) {
            $dados[] = $row->export(false);
        }
        $ret->data = $dados;
        $ret->collection = $dataset;
        $ret->ok = true;

      } catch (\Throwable $th) {
        $ret->msg = $th->getMessage();
      }
      return $ret->toJson();
    }

    public function find(Request $request, $id)
    {
      $ret = new RetApiController;
      try {
        $find = isset($id) ? $id : null;
        if (!$find) throw new Exception("Nenhum id informado");
        if ($find  == '') throw new Exception("Nenhum id informado");

        $row = Dispositivo::where('uuid', '=', $find)->first();
        if (!$row) throw new Exception("Nenhum dispositivo encontrado");

        $ret->data = $row->export(True);
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

        $rules = [
            'uuid' => ['string', 'min:1', 'max:45', 'required'],
            'descricao' => ['string', 'min:1', 'max:45', 'required'],
            'platform' => ['max:45'],
            'version' => ['max:45'],
            'model' => ['max:45'],
            'fabricante' => ['max:45']
        ];
        $messages = [
            'size' => 'O campo :attribute, deverá ter :max caracteres.',
            'integer' => 'O conteudo do campo :attribute deverá ser um número inteiro.',
            'unique' => 'O conteudo do campo :attribute já foi cadastrado.',
            'required' => 'O conteudo do campo :attribute é obrigatório.',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
            $msgs = [];
            $errors = $validator->errors();
            foreach ($errors->all() as $message) {
                $msgs[] = $message;
            }
            $ret->data = $msgs;
            throw new Exception(join("; ", $msgs));
        }

        $uuid = isset($request->uuid) ? $request->uuid : '';
        $row = Dispositivo::find($uuid);
        if (!$row) throw new Exception("Nenhuma dispositivo encontrado");

      } catch (\Throwable $th) {
        $ret->msg = $th->getMessage();
        return $ret->toJson();
      }

      try {
        DB::beginTransaction();

        $row->descricao = $request->descricao;
        $row->fabricante = $request->fabricante;
        $row->model = $request->model;
        $row->version = $request->version;
        $row->platform = $request->platform;
        $row->updated_usuarioid = $usuario->id;
        $row->save();

        DB::commit();

        $ret->id = $row->uuid;
        $ret->data = $row->export(true);
        $ret->ok = true;

      } catch (\Throwable $th) {
        DB::rollBack();
        $ret->msg = $th->getMessage();
      }


      return $ret->toJson();
    }

    public function allowToken(Request $request, $id)
    {
        $ret = new RetApiController;
        try {
        if($id=='')
            throw new Exception('Dispositivo não identificado!');

        $token = isset($request->token) ? $request->token : '';
        if($token=='')
            throw new Exception('Token não informado!');

        $link = DispositivoLink::where('uuid', $id)->where('token', $token)->first();
        if(!$link)
            throw new Exception('Link inválido');

        if($link->expire_at < Carbon::now())
            throw new Exception('Link expirado');

        if($link->expired != 0)
            throw new Exception('Link expirado');


        $dispositivo = Dispositivo::find($id);
        if(!$dispositivo)
            throw new Exception('Dispositivo não encontrado!');

        if($dispositivo->liberado)
            throw new Exception('Dispositivo foi liberado anteriormente!');

        } catch (\Throwable $th) {
            $ret->msg = $th->getMessage();
            return $ret->toJson();
        }
        try {
            DB::beginTransaction();

            $link->expired = 1;
            $link->save();

            $dispositivo->token = md5(uniqid(rand(), true));
            $dispositivo->tokenupdated_at = Carbon::now();
            $dispositivo->status = 1;
            $dispositivo->save();

            DB::commit();

            $ret->ok = true;

            //   try {
            //     event(new DispositivoAllowedEvent($dispositivo));
            //   } catch (\Throwable $th) {
            //   }

        } catch (\Throwable $th) {
            DB::rollBack();
            $ret->msg = $th->getMessage();
        }
        return $ret->toJson();
    }


    public function revokeToken(Request $request, $id)
    {
        $ret = new RetApiController;
        try {
            if($id=='')
                throw new Exception('Dispositivo não identificado!');

            $token = isset($request->token) ? $request->token : '';
            if($token=='')
                throw new Exception('Token não informado!');

            $link = DispositivoLink::where('uuid', $id)->where('token', $token)->first();
            if(!$link)
                throw new Exception('Link inválido');

            if($link->expire_at < Carbon::now())
                throw new Exception('Link expirado');

            if($link->expired!=0)
                throw new Exception('Link expirado');

            $dispositivo = Dispositivo::find($id);
            if(!$dispositivo)
                throw new Exception('Dispositivo não encontrado!');

            if($dispositivo->liberado)
                throw new Exception('Dispositivo foi liberado anteriormente!');

        } catch (\Throwable $th) {
            $ret->msg = $th->getMessage();
            return $ret->toJson();
        }
        try {
            DB::beginTransaction();

            $link->delete();

            $dispositivo->token = null;
            $dispositivo->tokenupdated_at = null;
            $dispositivo->tokenexpire_at = Carbon::now();
            $dispositivo->status = 2;
            $dispositivo->save();

            DB::commit();

            $ret->ok = true;

            // try {
            //     event(new DispositivoAllowedEvent($dispositivo));
            //   } catch (\Throwable $th) {
            //   }

        } catch (\Throwable $th) {
            DB::rollBack();
            $ret->msg = $th->getMessage();
        }
        return $ret->toJson();
    }

    public function delete(Request $request, $id)
    {
      $ret = new RetApiController;
      try {
        $find = isset($id) ? $id : '';
        if ($find === '') throw new Exception("Nenhum id informado");

        $row = Dispositivo::find($find);
        if (!$row) throw new Exception("Nenhum dispositivo encontrado");

        // if ($row->acertos) {
        //     if ($row->acertos->exists())
        //         throw new Exception('Não é possível excluir essa despesa pois existem acertos de viagens associados. Se necessário inative-a!');
        // }

      } catch (\Throwable $th) {
        $ret->msg = $th->getMessage();
        return $ret->toJson();
      }


      try {
        DB::beginTransaction();

        $del = $row->delete();

        DB::commit();

        $ret->msg = 'Dispositivo ' . $row->uuid . ' foi excluído!';
        $ret->ok = true;
      } catch (\Throwable $th) {
        DB::rollBack();
        $ret->msg = $th->getMessage();
      }

      return $ret->toJson();
    }
}
