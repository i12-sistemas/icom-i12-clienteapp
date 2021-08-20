<?php

namespace App\Http\Controllers\API\V1\mobile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Coletas;
use App\Models\ColetasNota;
use App\Models\ColetasEventos;
use App\Enums\ColetasSituacaoType;
use App\Enums\ColetasEncerramentoTipoType;

use App\Http\Controllers\RetApiController;
use Exception;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Validator;

class ColetasController extends Controller
{

    public function listall(Request $request)
    {
      $ret = new RetApiController;
      try {
        $motorista = session('motorista');
        if(!$motorista) throw new Exception('Nenhum motorista autenticado');

        $coletas = Coletas::with('itens', 'clientedestino','clienteorigem', 'motorista')
                          ->where('motoristaid', $motorista->id)
                          ->where('situacao', '=', '1')
                          ->whereNull('dhbaixa')
                          ->orderBy('id')->get();
        $dados = [];
        foreach ($coletas as $coleta) {
            $dados[] = $coleta->exportMotorista(true);
        }
        $ret->data = $dados;
        $ret->ok = true;
        // $ret->collection = $coletas;
      } catch (\Throwable $th) {
        $ret->msg = $th->getMessage();
      }
      return $ret->toJson();
    }


    public function baixa(Request $request)
    {
      $ret = new RetApiController;
      try {
        $dispositivo = session('dispositivo');
        if(!$dispositivo)
          throw new Exception('Nenhum dispositivo autenticado');

        $motorista = session('motorista');
        if(!$motorista)
          throw new Exception('Nenhum motorista autenticado');

        $avulsa = isset($request->avulsa) ? $request->avulsa : false;
        // se não for avulsa
        if (!$avulsa) {
          $dataencerramento = isset($request->dataencerramento) ? $request->dataencerramento : '';
          if(!$dataencerramento) throw new Exception('Data de encerramento inválida');
          if($dataencerramento == '') throw new Exception('Data de encerramento inválida');
          $dataencerramento = Carbon::createFromFormat('Y-m-d H:i:s', $dataencerramento);

          $idcoleta = isset($request->idcoleta) ? intVal($request->idcoleta) : 0;
          if(!($idcoleta > 0)) throw new Exception('Coleta não foi informada');
          $coleta = Coletas::find($idcoleta);
          if(!$coleta) throw new Exception('Coleta não encontrada');
          if($coleta->data_baixa) {
            $ret->ok = true;
            $ret->msg = 'Coleta #' . $coleta->id . ' já sincronizada anteriormente em ' . $coleta->data_baixa->format('d/m/Y H:i:s');
            $encerramento = [ 'id' => null, 'data' => $dataencerramento->format('Y-m-d H:i:s') ];
            if($coleta->encerramento)
              $encerramento = [ 'id' => $coleta->encerramento->id, 'data' => $coleta->encerramento->data->format('Y-m-d H:i:s') ];

            $ret->data = [ 'id' => $coleta->id,
                          'sync' => $encerramento
                          ];
            return $ret->toJson();
          }
        }

        $listabaixas = isset($request->baixas) ? $request->baixas : null;
        if(!is_array($listabaixas)) throw new Exception('Informa o campo "baixas" como Array - Lista de baixas');
        // if(count($listabaixas) <= 0) throw new Exception('Nenhuma baixa informada na lista');
        $rules = [
          'dhlocal_data' => ['required', 'date'],
          'localid' => ['required', 'integer'],
          'dhlocal_created_at'   => ['required', 'date'],
          'coletaavulsa'  => ['required', 'integer'],
          'motoristaid'  => ['required','integer'],
          'notanumero'  => ['required','integer'],
        ];
        $messages = [
          'required' => 'Campo :attribute é obrigatório.',
          'size' => 'Campo :attribute deve ter o tamanho exato de :size caracteres.',
          'max' => 'O campo :attribute, de valor :input, deverá ter no máximo :max caracteres.',
          'min' => 'O campo :attribute, de valor :input, deverá ter no mínimo :min caracteres.',
          'string' => 'O conteudo do campo :attribute deverá ser alfanúmerico.',
          'integer' => 'O conteudo do campo :attribute deverá ser número inteiro.',
          'date' => 'O conteudo do campo :attribute deverá ser data no padrão aaaa-mm-dd hh:mm:ss.',
        ];
        foreach ($listabaixas as $baixa) {
          $validator = Validator::make($baixa, $rules, $messages);
          if ($validator->fails()) {
            $msgs = [];
            $errors = $validator->errors();
            foreach ($errors->all() as $message) {
              $msgs[] = $message;
            }
            $ret->data = $msgs;
            throw new Exception(join("; ",$msgs));
          }
        }

      } catch (\Throwable $th) {
        $ret->msg = $th->getMessage();
        return $ret->toJson();
      }


      try {
        DB::beginTransaction();

        $adicionados = [];
        foreach ($listabaixas as $baixa) {
          $baixa = (object)$baixa;
          $docfiscal = isset($baixa->docfiscal) ? ($baixa->docfiscal === 'nfse' ? 'nfse' : 'nfe') : 'nfe';
          //check if exists
          if ($baixa->coletaavulsa == 1 ) {
            $idcoletaavulsa = ($baixa->coletaavulsa == 1 ) ? (isset($baixa->idcoletaavulsa) ? $baixa->idcoletaavulsa : null) : null;
            $newbaixa = ColetasNota::where('uuid', $dispositivo->uuid)
                             ->where('coletaavulsa', 1)
                             ->where('notachave', $baixa->notachave)
                             ->where('docfiscal', $docfiscal)
                             ->whereRaw('if(? > 0 , idcoletaavulsa=?, idcoletaavulsa is null)', [$idcoletaavulsa, $idcoletaavulsa])
                             ->first();
          } else {
            $newbaixa = ColetasNota::where('uuid', $dispositivo->uuid)
                             ->where('coletaavulsa', 0)
                             ->where('idcoleta', $baixa->idcoleta)
                             ->where('notachave', $baixa->notachave)
                             ->first();
          };

          if (!$newbaixa) {
            $newbaixa = new ColetasNota;
            $newbaixa->uuid = $dispositivo->uuid;
            $newbaixa->localid  = $baixa->localid ;
            $newbaixa->dhlocal_data = $baixa->dhlocal_data;
            $newbaixa->dhlocal_created_at = $baixa->dhlocal_created_at;
            $newbaixa->coletaavulsaincluida = 0;
            $newbaixa->coletaavulsa = $baixa->coletaavulsa;
            $newbaixa->idcoletaavulsa = ($baixa->coletaavulsa == 1 ) ? (isset($baixa->idcoletaavulsa) ? $baixa->idcoletaavulsa : null) : null;
            $newbaixa->idcoleta = ($baixa->coletaavulsa == 1 ) ? null : $baixa->idcoleta;
            $newbaixa->remetentecnpj = $baixa->remetentecnpj;
            $newbaixa->remetentenome = $baixa->remetentenome;
            $newbaixa->destinatariocnpj = $baixa->destinatariocnpj ? $baixa->destinatariocnpj : null;
            $newbaixa->destinatarionome = $baixa->destinatarionome ? $baixa->destinatarionome : null;
            $newbaixa->motoristaid = $baixa->motoristaid;
            $newbaixa->notanumero = $baixa->notanumero;
            $newbaixa->docfiscal = $docfiscal;
            if ($newbaixa->docfiscal === 'nfse') {
              $newbaixa->notadh = $baixa->notadh;
              $newbaixa->notavalor = $baixa->notavalor;
            } else {
              $newbaixa->notachave = $baixa->notachave;
            }
            $newbaixa->geo_error = isset($baixa->geo_error) ? $baixa->geo_error : null;
            $newbaixa->geo_latitude = isset($baixa->geo_latitude) ? $baixa->geo_latitude : null;
            $newbaixa->geo_longitude = isset($baixa->geo_longitude) ? $baixa->geo_longitude : null;
            $newbaixa->geo_altitude = isset($baixa->geo_altitude) ? $baixa->geo_altitude : null;
            $newbaixa->geo_accuracy = isset($baixa->geo_accuracy) ? $baixa->geo_accuracy : null;
            $newbaixa->geo_heading = isset($baixa->geo_heading) ? $baixa->geo_heading : null;
            $newbaixa->geo_speed = isset($baixa->geo_speed) ? $baixa->geo_speed : null;
            // $newbaixa->geo_timestamp = isset($baixa->geo_timestamp) ? $baixa->geo_timestamp : null;
            $newbaixa->obs = $baixa->obs;
            $newbaixa->save();

            $adicionados[] = [
              'localid' => $newbaixa->localid,
              'idsync' => $newbaixa->id,
              'synced_at' => $newbaixa->created_at->format('Y-m-d H:i:s')
            ];
          } else {
            $adicionados[] = [
              'localid' => $baixa->localid,
              'idsync' => $newbaixa->id,
              'synced_at' => $newbaixa->created_at->format('Y-m-d H:i:s')
            ];
          }
        }


        if (!$avulsa) {
            $coleta->dhbaixa = $newbaixa->dhlocal_data;
            $coleta->encerramentotipo = 2; //1 = Interno, 2 = Aplicativo motorista, 3 = Painel do cliente
            $coleta->situacao = ColetasSituacaoType::tcsEncerrado;
            // $coleta->updated_usuarioid = $usuario->id;
            $coleta->save();

        //   $encerra = new ColetaEncerra;
        //   $encerra->idcoleta = $coleta->id;
        //   $encerra->data = $dataencerramento;
        //   $encerra->idmotorista = $motorista->id;
        //   $encerra->uuid = $dispositivo->uuid;
        //   $encerra->save();
            $datajson = [
                'uuid' => $dispositivo->uuid,
                'motorista' => $motorista->id . ' - ' . $motorista->nome,
                'dataencerramento' => $dataencerramento,
            ];

            $logevento = new ColetasEventos();
            $logevento->created_at = Carbon::now();
            // $logevento->created_usuarioid = $usuario->id;
            $logevento->datajson  = json_encode($datajson);
            $logevento->created_motoristaid = $motorista->id;
            $logevento->coletaid = $coleta->id;
            $logevento->ip = getIp();
            $logevento->detalhe  = 'Nova coleta inserida';
            $logevento->tipo  = 'baixaapp';
            $ins = $logevento->save();
            if (!$ins) throw new Exception("Log de evento não foi inserido");
        }

        DB::commit();
        $ret->ok = true;
        $ret->msg = (!$avulsa) ? 'Coleta #' . $coleta->id . ' sincronizada' : 'Coleta avulsa sincronizada';
        if (!$avulsa) {
          $ret->data = [ 'id' => $coleta->id,
                        'sync' => [ 'id' => $logevento->id, 'data' => $logevento->created_at->format('Y-m-d H:i:s') ],
                        'itens' => $adicionados
                      ];
        } else {
          $ret->data = $adicionados;
        }

      } catch (\Throwable $th) {
        DB::rollBack();
        $ret->msg = $th->getMessage();
      }
      return $ret->toJson();
    }

}
