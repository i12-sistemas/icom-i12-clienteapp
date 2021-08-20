<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\MotoristaMsg;

use Validator;
use App\Http\Controllers\RetApiController;
use Exception;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class MotoristaMsgController extends Controller
{
    public function list(Request $request)
    {
      $ret = new RetApiController;
      try {
        $sortby = isset($request->sortby) ? $request->sortby : 'coletas.dhcoleta';
        $descending = isset($request->descending) ? $request->descending : 'asc';
        $dhcoletai = isset($request->dhcoletai) ? $request->dhcoletai : null;
        $dhcoletaf = isset($request->dhcoletaf) ? $request->dhcoletaf : null;
        $dhbaixai = isset($request->dhbaixai) ? $request->dhbaixai : null;
        $dhbaixaf = isset($request->dhbaixaf) ? $request->dhbaixaf : null;

        $find = isset($request->find) ? utf8_decode($request->find) : null;
        // $situacao = null;
        // if (isset($request->situacao)) {
        //     $situacao = explode(",", $request->situacao);
        //     if (!is_array($situacao)) $situacao[] = $situacao;
        //     $situacao = count($situacao) > 0 ? $situacao : null;
        // }
        // $origem = null;
        // if (isset($request->origem)) {
        //     $origem = explode(",", $request->origem);
        //     if (!is_array($origem)) $origem[] = $origem;
        //     $origem = count($origem) > 0 ? $origem : null;
        // }
        // $motoristas = null;
        // if (isset($request->motoristas)) {
        //     $motoristas = explode(",", $request->motoristas);
        //     if (!is_array($motoristas)) $motoristas[] = $motoristas;
        //     $motoristas = count($motoristas) > 0 ? $motoristas : null;
        // }



        $orderby = null;
        if (isset($request->orderby)) {
            $orderby = json_decode($request->orderby,true);
            $orderbynew = [];
            foreach ($orderby as $key => $value) {
                if ($key == 'clienteorigem') {
                    $lKey = 'trim(clienteorigem.razaosocial)';
                } else if ($key == 'clientedestino') {
                    $lKey = 'trim(clientedestino.razaosocial)';
                } else if ($key == 'motorista') {
                    $lKey = 'trim(motorista.nome)';
                } else if ($key == 'regiao') {
                    $lKey = 'cidadecoleta.regiaoid';
                } else if ($key == 'enderecocoleta') {
                    $lKey = 'concat(cidadecoleta.cidade,cidadecoleta.uf)';
                } else if ($key == 'cidadedestino') {
                    $lKey = 'concat(cidadedestino.cidade,cidadedestino.uf)';
                } else {
                    $lKey = 'coletas.' . $key;

                }
                $orderbynew[$lKey] = strtoupper($value);
            }
            if (count($orderbynew) > 0) {
                $orderby = $orderbynew;
            } else {
                $orderby = null;
            }
        }



        $perpage = isset($request->perpage) ? $request->perpage : 25;
        $dataset = MotoristaMsg::select(DB::raw('motorista_msg.*'))
                    // ->leftJoin('cliente as clienteorigem', 'coletas.origemclienteid', '=', 'clienteorigem.id')
                    // ->leftJoin('cliente as clientedestino', 'coletas.destinoclienteid', '=', 'clientedestino.id')
                    //     ->leftJoin('cidades as cidadedestino', 'clientedestino.cidadeid', '=', 'cidadedestino.id')
                    // ->leftJoin('cidades as cidadecoleta', 'coletas.endcoleta_cidadeid', '=', 'cidadecoleta.id')
                    // ->leftJoin('motorista', 'coletas.motoristaid', '=', 'motorista.id')
                    // ->with('coletacidade', 'coletaregiao', 'clienteorigem', 'clientedestino', 'motorista', 'created_usuario', 'updated_usuario', 'itens')
                    // ->when($find, function ($query) use ($find) {
                    //   return $query->where(function($query2) use ($find) {
                    //     return $query2->where('coletas.chavenota', 'like', $find.'%')
                    //             ->orWhere('coletas.gestaocliente_itenscomprador', 'like', $find.'%')
                    //             ->orWhere('coletas.gestaocliente_comprador', 'like', $find.'%')
                    //             // ->orWhere('coletas.especie', 'like', $find.'%')
                    //             // ->orWhere('coletas.endcoleta_logradouro', 'like', $find.'%')
                    //             // ->orWhere('coletas.endcoleta_endereco', 'like', $find.'%')
                    //             // ->orWhere('coletas.endcoleta_numero', 'like', $find.'%')
                    //             // ->orWhere('coletas.endcoleta_complemento', 'like', $find.'%')
                    //             ->orWhere('coletas.contatonome', 'like', $find.'%')
                    //             ->orWhere('coletas.contatoemail', 'like', $find.'%')
                    //             ->orWhere('coletas.obs', 'like', $find.'%')
                    //             // ->orWhere('coletas.endcoleta_bairro', 'like', $find.'%')
                    //             ->orWhere('coletas.endcoleta_cep', 'like', $find.'%')

                    //             ->orWhere('cidadecoleta.cidade', 'like', $find.'%')
                    //             ->orWhere('cidadecoleta.estado', 'like', $find.'%')
                    //             ->orWhere('cidadecoleta.uf', 'like', $find.'%')

                    //             ->orWhere('motorista.nome', 'like', $find.'%')
                    //             ->orWhere('motorista.apelido', 'like', $find.'%')
                    //             // ->orWhere('motorista.username', 'like', $find.'%')

                    //             ->orWhere('clienteorigem.razaosocial', 'like', $find.'%')
                    //             ->orWhere('clienteorigem.fantasia', 'like', $find.'%')
                    //             ->orWhere('clienteorigem.cnpj', 'like', $find.'%')
                    //             ->orWhere('clientedestino.razaosocial', 'like', $find.'%')
                    //             ->orWhere('clientedestino.fantasia', 'like', $find.'%')
                    //             ->orWhere('clientedestino.cnpj', 'like', $find.'%')
                    //             ;
                    //   });
                    // })
                    // ->when($numero, function ($query, $numero) {
                    //     return $query->Where('coletas.id', $numero);
                    // })
                    // ->when(isset($request->situacao) && ($situacao != null), function ($query, $t) use ($situacao) {
                    //     return $query->WhereIn('coletas.situacao', $situacao);
                    // })
                    // ->when($request->dhcoletai, function ($query) use ($dhcoletai) {
                    //     return $query->Where(DB::Raw('date(coletas.dhcoleta)'), '>=', $dhcoletai);
                    // })
                    // ->when($request->dhcoletaf, function ($query) use ($dhcoletaf) {
                    //     return $query->Where(DB::Raw('date(coletas.dhcoleta)'), '<=', $dhcoletaf);
                    // })
                    // ->when($request->dhbaixai, function ($query) use ($dhbaixai) {
                    //     return $query->Where(DB::Raw('date(coletas.dhbaixa)'), '>=', $dhbaixai);
                    // })
                    // ->when($request->dhbaixaf, function ($query) use ($dhbaixaf) {
                    //     return $query->Where(DB::Raw('date(coletas.dhbaixa)'), '<=', $dhbaixaf);
                    // })
                    // ->when($request->orderby, function ($query) use ($orderby) {
                    //     foreach ($orderby as $key => $value) {
                    //         $query->orderByRaw($key  . ' ' . $value);
                    //     }
                    //     return $query;
                    // })
                    ->orderBy('created_at', 'desc')
                    ->paginate($perpage);
        $dados = [];
        foreach ($dataset as $row) {
            $dados[] = $row->export(true);
        }

        $ret->data = $dados;
        $ret->sortby = $sortby;
        $ret->descending = ($descending == 'desc' ? 'desc' : 'asc');
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
        $find = isset($id) ? intVal($id) : 0;
        if (!($find>0)) throw new Exception("Nenhum id informado");

        $dataset = MotoristaMsg::find($find);
        if (!$dataset) throw new Exception("Mensagem não foi encontrada");

        $ret->data = $dataset->export(true);
        $ret->ok = true;

      } catch (\Throwable $th) {
        $ret->msg = $th->getMessage();
      }
      return $ret->toJson();
    }

    public function add(Request $request)
    {
      $ret = new RetApiController;
      try {
        $usuario = session('usuario');
        if(!$usuario)
          throw new Exception('Nenhum usuário autenticado');

        $rules = [
          'todos'   => ['required', 'boolean'],
          'titulo'  => ['required','string', 'min:1', "max:30"],
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

        if ($request->todos !== 1)
            if (!($request->motoristaparaid > 0))
                throw new Exception('Nenhum motorista informado.');

      } catch (\Throwable $th) {
        $ret->msg = $th->getMessage();
        return $ret->toJson();
      }

      try {

        DB::beginTransaction();

        $msg = new MotoristaMsg;
        $msg->denome = $usuario->nome;
        // $msg->detelefone  = $usuario->detelefone ? $request->detelefone : '';
        if ($request->todos) {
          $msg->todos = 1;
        } else {
          $msg->todos = 0;
          $msg->paraidmotorista = $request->motoristaparaid;
        }
        $msg->titulo = $request->titulo;
        $msg->msg = $request->msg;
        $msg->iduser = $usuario->id;
        $msg->ip = \Request::ip();
        $msg->save();

        DB::commit();

        $ret->ok = true;
        $ret->data = $msg->export(true);

        // event(new MensagemMotoristaEvent($msg));

      } catch (\Throwable $th) {
        DB::rollBack();
        $ret->msg = $th->getMessage();
      }
    //   try {
    //     $r = $this->sendMsgOneSignal($msg);
    //     if ((!$r->ok) && ($r->msg <> '')) throw new Exception($r->msg);
    //   } catch (\Throwable $th) {
    //     $ret->msg =' Mensagem cadastrada, mas ocorreu uma falha ao enviar notificação push - (Error: ' .  $th->getMessage() . ')';
    //   }
      return $ret->toJson();
    }

}
