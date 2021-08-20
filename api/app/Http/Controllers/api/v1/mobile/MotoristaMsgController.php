<?php

namespace App\Http\Controllers\API\V1\mobile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\MotoristaMsg;

use Validator;
use App\Http\Controllers\RetApiController;
use Exception;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

use App\Events\pusher\MensagemMotoristaEvent;

class MotoristaMsgController extends Controller
{
    public function listall(Request $request)
    {
      $ret = new RetApiController;
      try {
        $motorista = session('motorista');
          if(!$motorista)
            throw new Exception('Nenhum motorista autenticado');

        $lastmsg = isset($request->lastmsg) ? $request->lastmsg : null;
        if ($lastmsg) {
          $lastmsg = Carbon::createFromFormat('Y-m-d H:i:s', $lastmsg);
        }

        $page = isset($request->page) ? intval($request->page) : 1;
        if(!($page>0)) $page = 1;

        $pagesize = isset($request->pagesize) ? intval($request->pagesize) : 1000;
        if(!($pagesize>0)) $pagesize = 1000;

        $msgs = MotoristaMsg::whereRaw('((paraidmotorista=?) OR (idmotoristaresp=?) OR (todos=1))', [$motorista->id, $motorista->id])
                          ->whereRaw('if(? is null, true, created_at > ?)', [$lastmsg,$lastmsg])
                          ->orderBy('created_at', 'desc')
                          ->paginate($pagesize);
        $ret->ok = true;
        $ret->collection = $msgs;
        $ret->data = $msgs;
      } catch (\Throwable $th) {
        $ret->msg = $th->getMessage();
      }
      return $ret->toJson();
    }

    public function addRespostaMotorista(Request $request)
  {
    $ret = new RetApiController;
    try {
      $dispositivo = session('dispositivo');
      if(!$dispositivo)
        throw new Exception('Nenhum dispositivo autenticado');

      $motorista = session('motorista');
      if(!$motorista)
        throw new Exception('Nenhum motorista autenticado');

      $rules = [
        // 'denome'  => ['required','string', 'min:3', "max:70"],
        // 'detelefone'  => ['string', 'max:20'],
        // 'paraidmotorista'  => ['integer'],
        // 'todos'   => ['required', 'boolean'],
        // 'titulo'  => ['required','string', 'min:1', "max:30"],
        'msg'  => ['required','string', 'min:1', "max:500"]
      ];
      $messages = [
        'required' => 'Campo [:attribute] é obrigatório.',
        'size' => 'Campo [:attribute] deve ter o tamanho exato de :size caracteres.',
        'max' => 'O campo [:attribute], de valor :input, deve ter no máximo :max caracteres.',
        'min' => 'O campo [:attribute], de valor :input, deve ter no mínimo :min caracteres.',
        'string' => 'O conteudo do campo [:attribute] deve ser alfanúmerico.',
        'boolean' => 'O conteudo do campo [:attribute] deve ser boleano (true ou false).',
        'integer' => 'O conteudo do campo [:attribute] deve ser número inteiro.',
        'date' => 'O conteudo do campo [:attribute] deve ser data no padrão aaaa-mm-dd hh:mm:ss.',
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

    } catch (\Throwable $th) {
      $ret->msg = $th->getMessage();
      return $ret->toJson();
    }

    try {
      DB::beginTransaction();

      $msg = new MotoristaMsg;
      $msg->idmotoristaresp = $motorista->id;
      $msg->denome = $motorista->nome;
      $msg->detelefone  = 'Eu';
      $msg->todos = 0;
      $msg->titulo = 'Resposta';
      $msg->msg = $request->msg;
      $msg->ip = \Request::ip();
      $msg->save();

      DB::commit();

      $ret->ok = true;
      $ret->id = $msg->id;
      $ret->data = $msg;

    } catch (\Throwable $th) {
      DB::rollBack();
      $ret->msg = $th->getMessage();
    }

    // try {
    //   $r = $this->sendMsgOneSignal($msg);
    //   if ((!$r->ok) && ($r->msg <> '')) throw new Exception($r->msg);

    // } catch (\Throwable $th) {
    //   $ret->msg =' Mensagem cadastrada, mas ocorreu uma falha ao enviar notificação push - (Error: ' .  $th->getMessage() . ')';
    // }
    return $ret->toJson();
  }
}
