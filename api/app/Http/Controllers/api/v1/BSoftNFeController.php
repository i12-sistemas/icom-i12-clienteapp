<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Exception;
use Illuminate\Support\Facades\DB;
use Validator;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

use Illuminate\Support\Facades\Http;

use App\Http\Controllers\RetApiController;

use App\Models\ColetasNota;
use App\Models\NotaConferencia;
use App\Models\ColetasNotaXML;


class BSoftNFeController extends Controller
{

    // processa notas da tabela coletas_notas
    public function processa(Request $request)
    {
        $ret = new RetApiController;
        try {
            $limite = isset($request->pagesize) ? intval($request->pagesize) : 1;
            $limitetentativas = isset($request->limitetentativas) ? intval($request->limitetentativas) : 3;
            $delaydownload = isset($request->delaydownload) ? intval($request->delaydownload) : 0;
            $chaves = null;
            if (isset($request->chaves)) {
                $chaves = explode(",", $request->chaves);
                if (!is_array($chaves)) $chaves[] = $chaves;
                $chaves = count($chaves) > 0 ? $chaves : null;
            }


            // volta as notas consultando e pendente pra fila de download
            try {
                DB::beginTransaction();

                ColetasNota::whereRaw('trim(baixanfemsg) in  ("Consultando", "Pendente")')
                            ->whereRaw('date(created_at) >= date(date_add(now(), interval -1 month))')
                            ->where('baixanfestatus', '=', 2)
                            ->update([
                                'baixanfestatus' => 0,
                                'baixanfetentativas' => 0,
                                'baixanfemsg' => null
                            ]);

                ColetasNota::whereRaw('trim(baixanfemsg) = "Codigo: 400 - O CNPJ ou CPF do certificado não está autorizado a fazer o download do documento."')
                            ->whereRaw('date(created_at) >= date(date_add(now(), interval -1 month))')
                            ->whereRaw('date(baixanfedhproc) >= date(date_add(now(), interval -30 MINUTE))')
                            ->where('baixanfestatus', '>=', 2)
                            ->update([
                                'baixanfestatus' => 0,
                                'baixanfetentativas' => 0,
                                'baixanfemsg' => null
                            ]);

                DB::commit();
            } catch (\Throwable $th) {
                DB::rollBack();
                throw new Exception('Erro ao resetar nos pendentes e consultando - ' . $th->getMessage());
            }



            $notas = ColetasNota::where('baixanfestatus', '<>', 1)
                            ->where('docfiscal', '=', 'nfe')
                            ->when($request->chaves, function ($query) use ($chaves) {
                                return $query->whereIn('notachave', $chaves);
                            })
                            ->whereRaw('((ifnull(baixanfetentativas,0) <= ?) or (lcase(trim(baixanfemsg)) in  ("consultando", "pendente")))', [$limitetentativas])
                            ->orderBy('created_at', 'desc')
                            ->paginate($limite);
            if (!$notas) throw new Exception('Nenhum registro de chave para cadastrado');
            if (count($notas) == 0) throw new Exception('Nenhum registro de chave para cadastrado');

            $success = [];
            $error = [];
            foreach ($notas as $nota) {
                $retprocesso = self::download($nota->notachave, $delaydownload);
                try {
                    DB::beginTransaction();

                    $nota->identificaRemetente();

                    if ($retprocesso->ok) {
                        $nota->baixanfestatus = 1;
                        $nota->storagetipo = 'local';
                        $nota->storageurl = $retprocesso->data;
                        $nota->baixanfetentativas = 1;
                        $nota->baixanfemsg = '';
                        $nota->baixanfedhproc = Carbon::now();

                        $success[] = [
                            'chave' => $nota->notachave
                        ];
                    } else {
                        $nota->baixanfestatus = 2;
                        $nota->baixanfetentativas = intval($nota->baixanfetentativas) + 1;
                        $nota->baixanfemsg = $retprocesso->msg;
                        $nota->baixanfedhproc = Carbon::now();

                        $error[] = [
                            'chave' => $nota->notachave,
                            'error' => $retprocesso->msg
                        ];
                    }

                    $nota->save();

                    DB::commit();
                } catch (\Throwable $th) {
                    DB::rollBack();
                    throw new Exception('Erro ao salvar no banco de dados - ' . $th->getMessage());
                }

            }

            $ret->ok = true;
            $notas = ColetasNota::where('baixanfestatus', '<>', 1)
                            ->where('docfiscal', '=', 'nfe')
                            ->whereRaw('((baixanfetentativas is null) or (baixanfetentativas <= ?))', [$limitetentativas])
                            ->count();

            // $dados = [];
            // if (count($error) > 0)  $dados['error'] = $error;
            // if (count($success) > 0)  $dados['success'] = count($success);
            $ret->data = $notas;
        } catch (\Throwable $th) {
            $ret->msg = $th->getMessage();
        }
        return $ret->toJson();

    }

    // processa notas da tabela notas_conferencia almoxarifado
    public function processa_almoxarifado(Request $request)
    {
      $ret = new RetApiController;
      try {
          $limite = isset($request->pagesize) ? intval($request->pagesize) : 1;
          $limitetentativas = isset($request->limitetentativas) ? intval($request->limitetentativas) : 3;
          $delaydownload = isset($request->delaydownload) ? intval($request->delaydownload) : 0;
          $chaves = null;
          if (isset($request->chaves)) {
              $chaves = explode(",", $request->chaves);
              if (!is_array($chaves)) $chaves[] = $chaves;
              $chaves = count($chaves) > 0 ? $chaves : null;
          }

          $notas = NotaConferencia::where('baixanfestatus', '<>', 1)
                          ->when($request->chaves, function ($query) use ($chaves) {
                              return $query->whereIn('notachave', $chaves);
                          })
                          ->whereRaw('((baixanfetentativas is null) or (baixanfetentativas <= ?))', [$limitetentativas])
                          ->orderBy('created_at', 'desc')
                          ->paginate($limite);

          if (!$notas) throw new Exception('Nenhum registro de chave para cadastrado');
          if (count($notas) == 0) throw new Exception('Nenhum registro de chave para cadastrado');

          $success = [];
          $error = [];
          foreach ($notas as $nota) {
              $retprocesso = self::download($nota->notachave, $delaydownload);
              try {
                  DB::beginTransaction();

                  if ($retprocesso->ok) {
                      $nota->baixanfestatus = 1;
                      $nota->storagetipo = 'local';
                      $nota->storageurl = $retprocesso->data;
                      $nota->baixanfetentativas = 1;
                      $nota->baixanfemsg = '';

                      $success[] = [
                          'chave' => $nota->notachave
                      ];
                  } else {
                      $nota->baixanfestatus = 2;
                      $nota->baixanfetentativas = intval($nota->baixanfetentativas) + 1;
                      $nota->baixanfemsg = $retprocesso->msg;

                      $error[] = [
                          'chave' => $nota->notachave,
                          'error' => $retprocesso->msg
                      ];
                  }

                  $nota->save();

                  DB::commit();
              } catch (\Throwable $th) {
                  DB::rollBack();
                  throw new Exception('Erro ao salvar no banco de dados - ' . $th->getMessage());
              }
          }

          $ret->ok = true;
          $notas = NotaConferencia::where('baixanfestatus', '<>', 1)
                          ->whereRaw('((baixanfetentativas is null) or (baixanfetentativas <= ?))', [$limitetentativas])
                          ->count();

          // $dados = [];
          // if (count($error) > 0)  $dados['error'] = $error;
          // if (count($success) > 0)  $dados['success'] = count($success);
          $ret->data = $notas;
      } catch (\Throwable $th) {
          $ret->msg = $th->getMessage();
      }
      return $ret->toJson();

    }

    public function download($chave, $delaydownload = 0)
    {
      $ret = new RetApiController;
      try {
        $disk = Storage::disk('public');
        $strpath = 'nfe/xml';
        $file = $strpath . '/' . $chave . '.xml';
        if ($disk->exists($file)) {
            $ret->ok = true;
            $ret->data = $file;
            throw new Exception('Arquivo reaproveitado!');
        }

        $notainterna = ColetasNotaXML::where('chave', '=', $chave)->first();
        if ($notainterna) {
            $notainterna->exportFileBD($strpath);
            if ($disk->exists($file)) {
                $ret->ok = true;
                $ret->data = $file;
                throw new Exception('Arquivo reaproveitado do banco!');
            }
        }

        $token = env('BSOFT_TOKEN', '');
        if ($token === '')  throw new Exception('Token de acesso não foi configurado. (api download bsoft)');
        $delay = intval($delaydownload);

        $params = [
            'chave_acesso' => $chave
        ];
        $id_consulta = null;
        $response = Http::withToken($token)
                        ->asForm()
                        ->post('https://api.bsoft.com.br/docs/v2/consulta/sefaz', $params);
        if ($response->status() == 200) {
            $data = $response->json();
            $deucerto = ($data['status_consulta'] == 'Ok') && ($data['id_consulta'] !== '');
            if (!$deucerto)
                throw new Exception($data['status_consulta']);

            $id_consulta = $data['id_consulta'];
        } else {
            $data = $response->json();
            if ($data) {
                throw new Exception('Codigo: ' . $data['codigo'] . ' - ' . $data['descricao']);
            } else {
                $response->throw();
            }
        }

        if (!$id_consulta) throw new Exception('Nenhum id_consulta para consultar');

        $xmlbase64 = null;
        $params = [
            'id_consulta' => $id_consulta
        ];
        sleep($delay);
        $response = Http::withToken($token)
                        ->get('https://api.bsoft.com.br/docs/v2/retorno/sefaz', $params);

        if ($response->status() == 200) {
            $data = $response->json();
            $deucerto = ($data['status_consulta'] == 'Ok') && ($data['xml'] !== '');
            if (!$deucerto)
                throw new Exception('Status: ' . $data['status_consulta'] . ' - Erro: ' . $data['erro_consulta']);

            $xmlbase64 = $data['xml'];
        } elseif ($response->status() == 202) {
            $data = $response->json();
            throw new Exception('A consulta ainda está sendo processada - status: ' .  $data['status_consulta']);
        } else {
            $data = $response->json();
            if ($data) {
                throw new Exception('Codigo: ' . $data['codigo'] . ' - ' . $data['descricao']);
            } else {
                $response->throw();
            }
        }

        if (!$xmlbase64) throw new Exception('Nenhum xml para encontrado');


        $disk = Storage::disk('public');
        $strpath = 'nfe/xml';
        $path = $disk->path($strpath);
        if (!$disk->exists($strpath)) $disk->makeDirectory($strpath);

        $xmldata =  base64_decode($xmlbase64);

        $file = $strpath . '/' . $chave . '.xml';
        if (!$disk->exists($file)) $disk->delete($file);
        $disk->put($file, $xmldata);

        $ret->ok = true;
        $ret->data = $file;

      } catch (\Throwable $th) {
        $ret->msg = $th->getMessage();
      }
      return $ret;
    }


    // documentação
    // https://docs.bsoft.com.br/documentacao#documentos
    public function consulta_documentos(Request $request, $filtros, $outrosfiltros, $quantidade, $deslocamento = 0)
    {
      $ret = new RetApiController;
      try {
        $campos = "emitente_cnpj,emitente_nome,destinatario_cnpj,destinatario_nome,numero,valor,data_emissao,chave_acesso,peso";
        $token = ENV('BSOFT_TOKEN', '');

        $params = [
            'campos' => $campos,
            // 'cnpj_empresa' => $cnpj,
            'quantidade' => $quantidade,
            'deslocamento' => $deslocamento,
        ];

        if ($filtros !== '') $params['filtro'] = $filtros;

        if (is_array($outrosfiltros)) {
            foreach ($outrosfiltros as $key => $value) {
                $params[$key] = $value;
            }
        }
        $responseRet = Http::withToken($token)
                            ->timeout(90)
                            ->get('https://api.bsoft.com.br/docs/v2/documentos', $params);

        $data = [];
        if ($responseRet->status() == 200) {
            $body = json_decode($responseRet->body(), true);

            foreach ($body as $row) {
                if ($row) {
                    $data_emissao = Carbon::createFromFormat('d/m/Y', $row['data_emissao']);
                    $item = [
                        'id' => $row['id'],
                        'emitente_cnpj' => $row['emitente_cnpj'],
                        'emitente_nome' => $row['emitente_nome'],
                        'destinatario_cnpj' => $row['destinatario_cnpj'],
                        'destinatario_nome' => $row['destinatario_nome'],
                        'numero' => $row['numero'],
                        'chave_acesso' => $row['chave_acesso'],
                        'data_emissao' => $data_emissao->format('Y-m-d'),
                        'valor' => strtoFloat($row['valor']),
                        'peso' => strtoFloat($row['peso'])
                    ];
                    $data[] = $item;
                }
            }
        } else {
            $data = $responseRet;
            if ($data) {
                throw new Exception('Codigo: ' . $data['codigo'] . ' - ' . $data['descricao']);
            } else {
                $responseRet->throw();
            }
        }

        $ret->ok = true;
        $ret->data = $data;

      } catch (\Throwable $th) {
        $ret->msg = $th->getMessage();
      }
      return $ret;
    }

}
