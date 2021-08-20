<?php

namespace App\Http\Controllers\API\V1\mobile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Http\Controllers\RetApiController;
use Validator;
use Exception;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

use App\Jobs\SendMailLinkRequestAllowJob;

use App\Models\Dispositivo;
use App\Models\DispositivoLink;


class DispositivoController extends Controller
{
    public function store(Request $request)
    {
      $ret = new RetApiController;
      try {
        $rules = [
          'uuid'       => ['required', 'string', 'min:10'],
          'platform'   => ['required', 'string', 'min:1', "max:45"],
          'descricao'  => ['required', 'string', 'min:1', "max:45"],
          'fabricante'  => ['string', "max:45"],
          'version'  => ['string', "max:45"],
          'model'  => ['string', "max:45"],
          'descricao'  => ['string', "max:45"],
        ];
        $messages = [
          'uuid.required' => 'Campo :attribute é obrigatório.',
          'uuid.size' => 'Campo :attribute deve ter o tamanho exato de :size caracteres.',
          'uuid.unique' => 'Este dispositivo já está cadastrado.',
          'platform.required' => 'Campo :attribute é obrigatório.',
          'descricao.required' => 'Campo :attribute é obrigatório.',
          'max' => 'O campo :attribute, de valor :input, deverá ter no máximo :max caracteres.',
          'min' => 'O campo :attribute, de valor :input, deverá ter no mínimo :min caracteres.',
          'string' => 'O conteudo do campo :attribute deverá ser alfanúmerico.',
        ];
        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
          $msgs = [];
          $errors = $validator->errors();
          foreach ($errors->all() as $message) {
            $msgs[] = $message;
          }
          $ret->data = $msgs;
          throw new Exception(join("; ",$msgs));
        }
        $forceresendmail = isset($request->forceresendmail) ? $request->forceresendmail : false;
        $dispositivo = Dispositivo::find($request->uuid);
        if($dispositivo){
            if ($dispositivo->status === 2) throw new Exception('O acesso de dispositivo foi revogado e bloqueado! Se necessário consulte suporte técnico.');

            $ret->ok = true;
            if ($dispositivo->liberado) {
                $ret->id = $request->uuid;
                throw new Exception('Dispositivo liberado');
            } else {
                $retEmail = json_decode($this->requestLinkAllow($dispositivo->uuid)->content());
                $retEmail = (object) $retEmail;
                if ($retEmail->ok) {
                    throw new Exception('Solicitado uma nova liberação para o dispositivo cadastrado');
                } else {
                    $ret->ok = false;
                    throw new Exception('Falha ao solicitar liberação [' . $retEmail->msg . ']');
                }
            }
        }

      } catch (\Throwable $th) {
        $ret->msg = $th->getMessage();
        return $ret->toJson();
      }

      try {
        DB::beginTransaction();

        $dispositivo = new Dispositivo();
        $dispositivo->uuid = $request->uuid;
        $dispositivo->descricao = $request->descricao;
        $dispositivo->fabricante = $request->fabricante;
        $dispositivo->model = $request->model;
        $dispositivo->version = $request->version;
        $dispositivo->platform = $request->platform;
        $dispositivo->status = 0;
        $dispositivo->save();

        $retEmail = json_decode($this->requestLinkAllow($dispositivo->uuid)->content());
        $retEmail = (object) $retEmail;
        if (!$retEmail->ok)
          throw new Exception('Falha ao solicitar liberação [' . $retEmail->msg . ']');


        DB::commit();

        $ret->data = $dispositivo;
        $ret->ok = true;

      } catch (\Throwable $th) {
        DB::rollBack();
        $ret->msg = $th->getMessage();
      }
      return $ret->toJson();
    }


    public function requestLinkAllow($p_uuid)
    {

      $ret = new RetApiController;
      try {

        $uuid = isset($p_uuid) ? $p_uuid : '';
        if($uuid=='')
          throw new Exception('Dispositivo não identificado!');

        $email = $checklist = \App\auxiliares\Helper::getConfig('email_padrao_liberacao_dispositivo', '');
        if($email=='')
          throw new Exception('E-mail padrão não cadastrado!');

        $dispositivo = Dispositivo::find($uuid);
        if(!$dispositivo)
          throw new Exception('Dispositivo não identificado!');

        if($dispositivo->liberado)
          throw new Exception('Dispositivo já está liberado!');


      } catch (\Throwable $th) {
        $ret->msg = $th->getMessage();
        return $ret->toJson();
      }

      try {
        DB::beginTransaction();

        DispositivoLink::where('uuid', $dispositivo->uuid)
                              ->where('expired', 0)
                              ->delete();

        $link = new DispositivoLink;
        $link->token = md5($dispositivo->uuid . Carbon::now());
        $link->uuid = $dispositivo->uuid;
        $link->email = $email;
        $request = new Request;
        $link->ip = \Request::ip();
        $link->expired = 0;
        $link->expire_at = Carbon::now()->addHour(1);
        $link->save();

        DB::commit();

        $ret->ok = true;

        $link = DispositivoLink::where('uuid', $dispositivo->uuid)
                               ->where('email', $email)
                               ->where('expired', 0)
                               ->first();
        SendMailLinkRequestAllowJob::dispatch($dispositivo, $link);
      } catch (\Throwable $th) {
        DB::rollBack();
        $ret->ok = false;
        $ret->msg = $th->getMessage();
      }

      return $ret->toJson();
    }


    public function auth(Request $request)
    {

        $ret = new RetApiController;
        try {
        $uuid = isset($request->uuid) ? $request->uuid : '';
        if($uuid=='')
            throw new Exception('Dispositivo não identificado!');

        $dispositivo = Dispositivo::where('uuid', $uuid)->first();
        if(!$dispositivo) throw new Exception('Dispositivo não encontrado!');

        if(!$dispositivo->token) throw new Exception('Dispositivo bloqueado.');

        if($dispositivo->token == '')
        throw new Exception('Token inválido');

        if(!$dispositivo->liberado)
            throw new Exception('Dispositivo foi liberado!');


        } catch (\Throwable $th) {
        $ret->msg = $th->getMessage();
        return $ret->toJson();
        }


        try {
        DB::beginTransaction();

        $dispositivo->tokenexpire_at = Carbon::now()->addYears();
        $dispositivo->accesscode = md5($dispositivo->token . $dispositivo->tokenexpire_at->format('Ymdhis') . 'Conect@');
        $dispositivo->save();

        DB::commit();

        $ret->data = ['token' => $dispositivo->token,
                        'accesscode' => $dispositivo->accesscode,
                        'tokenexpire_at' => $dispositivo->tokenexpire_at->format('Y-m-d h:i:s')
                    ];
        $ret->ok = true;

        } catch (\Throwable $th) {
        DB::rollBack();
        $ret->msg = $th->getMessage();
        }
        return $ret->toJson();
    }
}
