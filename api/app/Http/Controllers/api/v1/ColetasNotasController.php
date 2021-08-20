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
use App\Models\Coletas;
use App\Models\ColetasNotaXMLToken;
use App\Models\Cliente;
use App\Models\Cidades;

use App\Jobs\ColetasNotas\EmailTokenXMLPendentePorUsuarioJob;

class ColetasNotasController extends Controller
{

    public function list(Request $request)
    {
      $ret = new RetApiController;
      try {
        $sortby = isset($request->sortby) ? $request->sortby : 'coletas_nota.dhlocal_data';
        $descending = isset($request->descending) ? $request->descending : 'asc';
        $dhlocal_datai = isset($request->dhlocal_datai) ? $request->dhlocal_datai : null;
        $dhlocal_dataf = isset($request->dhlocal_dataf) ? $request->dhlocal_dataf : null;
        $find = isset($request->find) ? utf8_decode($request->find) : null;
        $coletaavulsa = isset($request->coletaavulsa) ? $request->coletaavulsa : null;
        $notanumero = isset($request->notanumero) ? intval($request->notanumero) : null;
        $idcoleta = isset($request->idcoleta) ? intval($request->idcoleta) : null;

        $id = isset($request->id) ? intval($request->id) : null;
        $remetentenome = isset($request->remetentenome) ? $request->remetentenome : null;
        $remetentecnpj = isset($request->remetentecnpj) ? cleanDocMask($request->remetentecnpj) : null;
        $destinatarionome = isset($request->destinatarionome) ? $request->destinatarionome : null;
        $destinatariocnpj = isset($request->destinatariocnpj) ? cleanDocMask($request->destinatariocnpj) : null;
        $motorista = isset($request->motorista) ? $request->motorista : null;
        $omitircomcargaentrada = isset($request->omitircomcargaentrada) ? boolVal($request->omitircomcargaentrada) : false;

        $status = null;
        $errodownload = null;
        $pendenteprocessarxml = null;
        $xmlignorado = null;
        $xmlok = null;
        if (isset($request->status)) {
            $status = json_decode($request->status,true);
            if (!is_array($status)) {
                $status = [];
                $status[] = $request->status;
            }
            $status = count($status) > 0 ? $status : null;

            if (in_array('1', $status)) $xmlok=true;

            if (in_array('2', $status)) $baixanfestatus[] = 0;
            if (in_array('3', $status)) $pendenteprocessarxml = true;
            if (in_array('4', $status)) $errodownload = true;
            if (in_array('5', $status)) $xmlignorado = true;
        }

        $orderby = null;
        if (isset($request->orderby)) {
            $orderby = json_decode($request->orderby,true);
            $orderbynew = [];
            foreach ($orderby as $key => $value) {
                if ($key == 'dhlocal_data') {
                    $lKey = 'coletas_nota.dhlocal_data';
                } else if ($key == 'status') {
                    $lKey = 'coletas_nota.xmlprocessado';
                } else if ($key == 'motorista') {
                    $lKey = 'trim(motorista.nome)';
                } else {
                    $lKey = 'coletas_nota.' . $key;

                }
                $orderbynew[$lKey] = strtoupper($value);
                if ($key == 'status') $orderbynew['coletas_nota.baixanfestatus'] = strtoupper($value);
                $orderbynew['coletas_nota.id'] = strtoupper($value);
            }
            if (count($orderbynew) > 0) {
                $orderby = $orderbynew;
            } else {
                $orderby = null;
            }
        }


        // se existir chave, cancela outros filtros
        $chave = isset($request->chave) ? $request->chave : null;
        if ($chave) {
            if ($chave == '') $chave = null;

            if ($chave != '') {
                // $dhcoletaf = null;
                // $dhcoletaf = null;
                // $dhbaixai = null;
                // $dhbaixaf = null;
                // $situacao = null;
                // $origem = null;
                $find  = null;
            }
        // } else {
        //     if ($find != '') {
        //         $n = intval($find);
        //         if ($n > 0) $numero = $n;
        //     }
        }

        $perpage = isset($request->perpage) ? $request->perpage : 25;
        $dataset = ColetasNota::select(DB::raw('coletas_nota.*'))
                    ->leftJoin('cargaentradaitem', 'coletas_nota.notachave', '=', 'cargaentradaitem.nfechave')
                    // ->leftJoin('cliente as clientedestino', 'coletas.destinoclienteid', '=', 'clientedestino.id')
                    //     ->leftJoin('cidades as cidadedestino', 'clientedestino.cidadeid', '=', 'cidadedestino.id')
                    // ->leftJoin('cidades as cidadecoleta', 'coletas.endcoleta_cidadeid', '=', 'cidadecoleta.id')
                    ->leftJoin('motorista', 'coletas_nota.motoristaid', '=', 'motorista.id')
                    ->with('motorista', 'coleta')
                    ->when(isset($request->find), function ($query) use ($find) {
                        $n = intval($find);
                        return $query->where('coletas_nota.notachave', 'like', '%'.cleanDocMask($find).'%')
                                ->orWhere('coletas_nota.remetentecnpj', 'like', '%'.cleanDocMask($find).'%')
                                ->orWhere('coletas_nota.destinatariocnpj', 'like', '%'.cleanDocMask($find).'%')
                                ->orWhere('coletas_nota.destinatarionome', 'like', '%'.$find.'%')
                                ->orWhere('coletas_nota.remetentenome', 'like', '%'.$find.'%')
                                ->orWhere('coletas_nota.obs', 'like', '%'.$find.'%')
                                ->orWhere('coletas_nota.endcoleta_endereco', 'like', '%'.$find.'%')
                                ->orWhere('coletas_nota.endcoleta_bairro', 'like', '%'.$find.'%')
                                ->orWhereRaw('if(? > 0, coletas_nota.notanumero = ?, false)', [$n, $n])
                                ->orWhereRaw('if(? > 0, coletas_nota.idcoleta = ?, false)', [$n, $n])
                                ->orWhereRaw('if(? > 0, coletas_nota.idcoletaavulsa = ?, false)', [$n, $n])
                                ;
                    })
                    ->when($request->has('omitircomcargaentrada') && $omitircomcargaentrada, function ($query) {
                        return $query->WhereRaw('cargaentradaitem.id is null');
                    })
                    ->when(isset($request->id), function ($query) use ($id) {
                        return $query->Where('coletas_nota.id', '=', $id);
                    })
                    ->when(isset($request->remetentenome), function ($query) use ($remetentenome) {
                        return $query->Where('coletas_nota.remetentenome', 'like', '%'.$remetentenome.'%');
                    })
                    ->when(isset($request->destinatarionome), function ($query) use ($destinatarionome) {
                        return $query->Where('coletas_nota.destinatarionome', 'like', '%'.$destinatarionome.'%');
                    })
                    ->when(isset($request->remetentecnpj), function ($query) use ($remetentecnpj) {
                        return $query->Where('coletas_nota.remetentecnpj', 'like', '%'.$remetentecnpj.'%');
                    })
                    ->when(isset($request->destinatariocnpj), function ($query) use ($destinatariocnpj) {
                        return $query->Where('coletas_nota.destinatariocnpj', 'like', '%'.$destinatariocnpj.'%');
                    })
                    ->when(isset($request->motorista) && ($motorista ? $motorista !== '' : false), function ($query) use ($motorista)  {
                        return $query->where(function($query2) use ($motorista) {
                            return $query2->where('motorista.nome', 'like', '%'.$motorista.'%')
                            ->orWhere('motorista.apelido', 'like', '%'.$motorista.'%');
                        });
                    })
                    ->when(isset($request->chave), function ($query) use ($chave) {
                        return $query->Where('coletas_nota.notachave', '=', $chave);
                    })
                    ->when(isset($request->idcoleta), function ($query) use ($idcoleta) {
                        return $query->Where('coletas_nota.idcoleta', '=', $idcoleta);
                    })
                    ->when(isset($request->notanumero), function ($query) use ($notanumero) {
                        return $query->Where('coletas_nota.notanumero', '=', $notanumero);
                    })
                    ->when(isset($request->coletaavulsa), function ($query) use ($coletaavulsa) {
                        if ($coletaavulsa == 'AP') {
                            return $query->Where('coletas_nota.coletaavulsa', '=', 1)
                                ->whereRaw('((coletas_nota.coletaavulsaignorada=0) or (coletas_nota.coletaavulsaignorada is null))')
                                ->whereRaw('coletas_nota.idcoleta is null');
                        }
                        if ($coletaavulsa == 'AO') {
                            return $query->Where('coletas_nota.coletaavulsa', '=', 1)
                                ->whereRaw('((coletas_nota.coletaavulsaignorada=0) or (coletas_nota.coletaavulsaignorada is null))')
                                ->whereRaw('not(coletas_nota.idcoleta is null)');
                        }
                        if ($coletaavulsa == 'A') {
                            return $query->Where('coletas_nota.coletaavulsa', '=', 1)
                            ->whereRaw('((coletas_nota.coletaavulsaignorada=0) or (coletas_nota.coletaavulsaignorada is null))');
                        }
                    })
                    ->when(isset($request->status), function ($query, $t) use ($xmlignorado, $errodownload, $pendenteprocessarxml,$xmlok) {
                        return $query->when(isset($xmlok), function ($query2) use ($xmlok) {
                            return $query2->Where('coletas_nota.xmlprocessado', '=', 1)
                                    ->where('coletas_nota.baixanfestatus', '=', 1);
                        })
                        ->when(isset($pendenteprocessarxml), function ($query2) use ($pendenteprocessarxml) {
                            return $query2->Where('coletas_nota.xmlprocessado', '!=', 1)
                                    ->where('coletas_nota.baixanfestatus', '=', 1);
                        })
                        ->when(isset($errodownload), function ($query2) use ($errodownload) {
                            return $query2->Where('coletas_nota.xmlprocessado', '!=', 1)
                                    ->where('coletas_nota.baixanfestatus', '=', 2);
                        })
                        ->when(isset($xmlignorado), function ($query2) use ($xmlignorado) {
                            return $query2->Where('coletas_nota.xmlprocessado', '!=', 1);
                        });
                    })
                    ->when(isset($request->dhlocal_datai), function ($query) use ($dhlocal_datai) {
                        return $query->Where(DB::Raw('date(coletas_nota.dhlocal_data)'), '>=', $dhlocal_datai);
                    })
                    ->when(isset($request->dhlocal_dataf), function ($query) use ($dhlocal_dataf) {
                        return $query->Where(DB::Raw('date(coletas_nota.dhlocal_data)'), '<=', $dhlocal_dataf);
                    })
                    ->when(isset($request->orderby), function ($query) use ($orderby) {
                        foreach ($orderby as $key => $value) {
                            $query->orderByRaw($key  . ' ' . $value);
                        }
                        return $query;
                    })
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


    // public function testa(Request $request, $chave)
    // {
    //   $ret = new RetApiController;
    //   try {
    //       $nota = ColetasNota::where('notachave', '=', $chave)->first();
    //       if (!$nota) throw new Exception('Nenhum registro a ser processado');

    //       $disk = Storage::disk('public');
    //       $arquivoxml = $disk->get($nota->storageurl);
    //       // Transformando o conteúdo XML da variável $string em Objeto
    //       $xml = simplexml_load_string($arquivoxml);

    //       $dhEmi = $xml->NFe->infNFe->ide->dhEmi;
    //       $d = substr($dhEmi, 0, 10);
    //       $t = substr($dhEmi, 11, 8);
    //       $tz = substr($dhEmi, 19, 6);
    //       $dhEmi = Carbon::createFromFormat('Y-m-d H:i:s', $d . ' ' . $t);
    //       $dhEmi->setTimezone($tz);

    //       $clienteorigem = Cliente::where('cnpj', '=', $xml->NFe->infNFe->emit->CNPJ)->first();
    //       if (!$clienteorigem) throw new Exception('Nenhum cliente de origem encontrado com o CNPJ emitente ' . $xml->NFe->infNFe->emit->CNPJ);


    //       $cc = app()->make('App\Http\Controllers\api\v1\ClienteController');
    //       $ret1 = app()->call([$cc, 'addNovoComXML'], ['pXML' => $xml, 'pTag' => 'emit', 'pObs' => 'Leitura de XML de nota da coleta :: cadastro como emissor da NF-e']);
    //       $ret2 = app()->call([$cc, 'addNovoComXML'], ['pXML' => $xml, 'pTag' => 'dest', 'pObs' => 'Leitura de XML de nota da coleta :: cadastro como destinatário da NF-e']);

    //       dd([
    //         $xml->NFe->infNFe->emit,
    //         $ret1,
    //         $xml->NFe->infNFe->dest,
    //         $ret2,
    //       ]);

    //   } catch (\Throwable $th) {
    //       $ret->msg = $th->getMessage();
    //   }
    //   return $ret->toJson();
    // }

    //index xml nfe para tabela
    public function processa(Request $request)
    {
        $ret = new RetApiController;
        try {
            $limite = isset($request->pagesize) ? intval($request->pagesize) : 20;
            $chaves = null;
            if (isset($request->chaves)) {
                $chaves = explode(",", $request->chaves);
                if (!is_array($chaves)) $chaves[] = $chaves;
                $chaves = count($chaves) > 0 ? $chaves : null;
            }

            $notas = ColetasNota::where('baixanfestatus', '=', 1)
                            ->where('xmlprocessado', '=', 0)
                            ->where('docfiscal', '=', 'nfe')
                            ->when($request->chaves, function ($query) use ($chaves) {
                                return $query->whereIn('notachave', $chaves);
                            })
                            ->orderBy('created_at', 'desc')
                            ->paginate($limite);
            if (!$notas) throw new Exception('Nenhum registro a ser processado');
            if (count($notas) == 0) throw new Exception('Nenhum registro a ser processado');

            $disk = Storage::disk('public');

            foreach ($notas as $nota) {
                // if (!$disk->exists($file)) $disk->delete($file);
                $arquivoxml = $disk->get($nota->storageurl);
                // $arquivoxml = $nota->storageurl;

                // Transformando o conteúdo XML da variável $string em Objeto
                $xml = simplexml_load_string($arquivoxml);

                $dhEmi = $xml->NFe->infNFe->ide->dhEmi;
                $d = substr($dhEmi, 0, 10);
                $t = substr($dhEmi, 11, 8);
                $tz = substr($dhEmi, 19, 6);
                $dhEmi = Carbon::createFromFormat('Y-m-d H:i:s', $d . ' ' . $t);
                $dhEmi->setTimezone($tz);

                $clienteorigem = Cliente::where('cnpj', '=', $xml->NFe->infNFe->emit->CNPJ)->first();
                $clientedestino = Cliente::where('cnpj', '=', $xml->NFe->infNFe->dest->CNPJ)->first();

                $qVol = 0;
                $pesoB = 0;
                $esp = '';
                foreach ($xml->NFe->infNFe->transp->vol as $key => $vol) {
                    $qVol = $qVol + floatVal($vol->qVol);
                    $pesoB = $pesoB + floatVal($vol->pesoB);
                    $esp = $vol->esp;
                }

                try {
                    DB::beginTransaction();

                    $nota->xmlprocessado = 1;
                    $nota->notanumero = $xml->NFe->infNFe->ide->nNF;
                    $nota->notaserie = $xml->NFe->infNFe->ide->serie;
                    $nota->notadh = $dhEmi;
                    $nota->qtde = $qVol;
                    $nota->peso = $pesoB;
                    $nota->especie = $esp;

                    $nota->remetentecnpj = $xml->NFe->infNFe->emit->CNPJ;
                    $nota->remetentenome = $xml->NFe->infNFe->emit->xNome;
                    if ($clienteorigem) $nota->remetenteid = $clienteorigem->id;

                    $nota->destinatariocnpj = $xml->NFe->infNFe->dest->CNPJ;
                    $nota->destinatarionome = $xml->NFe->infNFe->dest->xNome;
                    if ($clientedestino) $nota->destinatarioid = $clientedestino->id;

                    $nota->notavalor = $xml->NFe->infNFe->total->ICMSTot->vNF;

                    $nota->endcoleta_cidadecodibge = $xml->NFe->infNFe->emit->enderEmit->cMun;
                    $nota->endcoleta_endereco = $xml->NFe->infNFe->emit->enderEmit->xLgr;
                    $nota->endcoleta_numero = $xml->NFe->infNFe->emit->enderEmit->nro;
                    $nota->endcoleta_bairro = $xml->NFe->infNFe->emit->enderEmit->xBairro;
                    $nota->endcoleta_cep = $xml->NFe->infNFe->emit->enderEmit->CEP;
                    $nota->endcoleta_complemento = $xml->NFe->infNFe->emit->enderEmit->xCpl;

                    $nota->save();

                    DB::commit();
                } catch (\Throwable $th) {
                    DB::rollBack();
                    throw new Exception('Erro ao salvar no banco de dados - ' . $th->getMessage());
                }

            }

            $notas = ColetasNota::where('baixanfestatus', '=', 1)
                        ->where('xmlprocessado', '=', 0)
                        ->where('docfiscal', '=', 'nfe')
                        ->count();
            $ret->ok = true;
            $ret->data = $notas;
        } catch (\Throwable $th) {
            $ret->msg = $th->getMessage();
        }
        return $ret->toJson();

    }


    // procedure para criar coleta avulsa
    public function processaColetaAvulsa(Request $request)
    {
        $ret = new RetApiController;
        try {
            $limite = isset($request->pagesize) ? intval($request->pagesize) : 20;
            $chaves = null;
            if (isset($request->chaves)) {
                $chaves = explode(",", $request->chaves);
                if (!is_array($chaves)) $chaves[] = $chaves;
                $chaves = count($chaves) > 0 ? $chaves : null;
            }


            $notas = ColetasNota::where('xmlprocessado', '=', 1)
                        ->where('docfiscal', '=', 'nfe')
                        ->where('coletaavulsa', '=', 1)
                        ->where('coletaavulsantentativa', '<', 4)
                        ->where('baixanfestatus', '=', 1)
                        ->where('coletaavulsaignorada', '=', 0)
                        ->where('coletaavulsaincluida', '=', 0)
                        ->whereRaw('idcoleta is null')
                        ->when($request->chaves, function ($query) use ($chaves) {
                            return $query->whereIn('notachave', $chaves);
                        })
                        ->orderBy('dhlocal_data', 'desc')
                        ->orderBy('created_at', 'desc')
                        ->paginate($limite);

            if (!$notas) throw new Exception('Nenhum registro a ser processado');
            if (count($notas) == 0) throw new Exception('Nenhum registro a ser processado');

            $disk = Storage::disk('public');

            $sucesso = [];
            $erro = [];
            foreach ($notas as $nota) {
                try {
                    // if (!$disk->exists($file)) $disk->delete($file);
                    $arquivoxml = $disk->get($nota->storageurl);
                    // $arquivoxml = $nota->storageurl;

                    // Transformando o conteúdo XML da variável $string em Objeto
                    $xml = simplexml_load_string($arquivoxml);

                    $dhEmi = $xml->NFe->infNFe->ide->dhEmi;
                    $d = substr($dhEmi, 0, 10);
                    $t = substr($dhEmi, 11, 8);
                    $tz = substr($dhEmi, 19, 6);
                    $dhEmi = Carbon::createFromFormat('Y-m-d H:i:s', $d . ' ' . $t);
                    $dhEmi->setTimezone($tz);

                    $clienteorigem = Cliente::where('cnpj', '=', $xml->NFe->infNFe->emit->CNPJ)->first();
                    if (!$clienteorigem) {
                        $cc = app()->make('App\Http\Controllers\api\v1\ClienteController');
                        $ret = app()->call([$cc, 'addNovoComXML'], ['pXML' => $xml, 'pTag' => 'emit', 'pObs' => 'Leitura de XML de nota da coleta :: cadastro como emissor da NF-e']);
                        if (!$ret->ok) throw new Exception('Erro ao cadastrar cliente - ' . $ret->msg);
                        $clienteorigem = $ret->data;
                    }
                    if (!$clienteorigem) throw new Exception('Nenhum cliente de origem encontrado com o CNPJ emitente ' . $xml->NFe->infNFe->emit->CNPJ);


                    $clientedestino = Cliente::where('cnpj', '=', $xml->NFe->infNFe->dest->CNPJ)->first();
                    if (!$clientedestino) {
                      $cc = app()->make('App\Http\Controllers\api\v1\ClienteController');
                      $ret = app()->call([$cc, 'addNovoComXML'], ['pXML' => $xml, 'pTag' => 'dest', 'pObs' => 'Leitura de XML de nota da coleta :: cadastro como destinatário da NF-e']);
                      if (!$ret->ok) throw new Exception('Erro ao cadastrar cliente - ' . $ret->msg);
                      $clientedestino = $ret->data;
                    }
                    if (!$clientedestino) throw new Exception('Nenhum cliente de destino encontrado com o CNPJ emitente ' . $xml->NFe->infNFe->dest->CNPJ);

                    $cMunColeta = $xml->NFe->infNFe->dest->enderDest->cMun;
                    $cidadeColeta = Cidades::where('codigo_ibge', '=', $cMunColeta)->first();
                    if (!$cidadeColeta) throw new Exception('Cidade da coleta não foi encontrada com código ' . $cMunColeta);

                    $qVol = 0;
                    $pesoB = 0;
                    $esp = '';
                    foreach ($xml->NFe->infNFe->transp->vol as $key => $vol) {
                        $qVol = $qVol + floatVal($vol->qVol);
                        $pesoB = $pesoB + floatVal($vol->pesoB);
                        $esp = $vol->esp;
                    }

                    try {
                        DB::beginTransaction();

                        $coleta = new Coletas();
                        $coleta->created_usuarioid =0; // sistema-auto
                        $coleta->origem = '4'; // 4=Coleta avulsa aplicativo
                        $coleta->origemclienteid = $clienteorigem->id;
                        $coleta->destinoclienteid = $clientedestino->id;
                        $coleta->motoristaid = $nota->motoristaid;
                        $coleta->chavenota = ($nota->notachave == '' ? null : $nota->notachave);
                        $coleta->dhcoleta = $nota->dhlocal_data;
                        $coleta->dhbaixa = $nota->created_at;
                        // $coleta->contatonome = $request->contatonome;
                        // $coleta->contatoemail = $request->contatoemail;
                        $coleta->peso = $pesoB;
                        $coleta->especie = $esp;
                        $coleta->qtde = $qVol;
                        $coleta->obs = utf8_decode($xml->NFe->infNFe->infAdic->infCpl);

                        $coleta->veiculoexclusico = 0;
                        $coleta->cargaurgente = 0;
                        $coleta->produtosperigosos = 0;
                        $coleta->situacao = '2'; //  2 = Encerrado
                        $coleta->encerramentotipo = '2'; //  2 = Aplicativo motorista
                        $coleta->endcoleta_logradouro = utf8_decode($xml->NFe->infNFe->dest->enderDest->xLgr);
                        $coleta->endcoleta_endereco = utf8_decode($xml->NFe->infNFe->dest->enderDest->xLgr);
                        $coleta->endcoleta_numero = utf8_decode($xml->NFe->infNFe->dest->enderDest->nro);
                        $coleta->endcoleta_bairro = utf8_decode($xml->NFe->infNFe->dest->enderDest->xBairro);
                        $coleta->endcoleta_cep = $xml->NFe->infNFe->dest->enderDest->CEP;
                        $coleta->endcoleta_cidadeid = $cidadeColeta->id;
                        $coleta->updated_usuarioid = 0;
                        $coleta->save();

                        $nota->coletaavulsaincluida = 1;
                        $nota->idcoleta = $coleta->id;
                        $nota->save();

                        DB::commit();

                        $sucesso[] = [
                            'chave' => $nota->notachave,
                            'coletaid' => $coleta->id
                        ];
                    } catch (\Throwable $th) {
                        DB::rollBack();
                        throw new Exception($th->getMessage());
                    }

                } catch (\Throwable $th) {
                    try {
                        DB::beginTransaction();

                        $nota->coletaavulsaincluida = 0;
                        $nota->idcoleta = null;
                        $nota->coletaavulsantentativa = $nota->coletaavulsantentativa ? $nota->coletaavulsantentativa+1 : 1;
                        $nota->coletaavulsaerror = 1;
                        $nota->coletaavulsaerrormsg = $th->getMessage();
                        $nota->save();

                        DB::commit();
                    } catch (\Throwable $th) {
                        DB::rollBack();
                        throw new Exception('Erro ao salvar erro no banco de dados - ' . $th->getMessage());
                    }
                    $erro[] = [
                        'chave' => $nota->notachave,
                        'erro' => $th->getMessage()
                    ];
                }

            }

            $restante = ColetasNota::where('xmlprocessado', '=', 1)
                                    ->where('docfiscal', '=', 'nfe')
                                    ->where('coletaavulsa', '=', 1)
                                    ->where('coletaavulsantentativa', '<', 4)
                                    ->where('baixanfestatus', '=', 1)
                                    ->where('coletaavulsaignorada', '=', 0)
                                    ->where('coletaavulsaincluida', '=', 0)
                                    ->whereRaw('idcoleta is null')
                                    ->count();

            $ret->ok = true;
            $ret->data = [
                'restante'   =>  $restante,
                'sucesso'   =>  $sucesso,
                'erro'   =>  $erro
            ];
        } catch (\Throwable $th) {
            $ret->msg = $th->getMessage();
        }
        return $ret->toJson();

    }


    public function addLinkInputXMLNFe(Request $request)
    {
      $ret = new RetApiController;
      try {

        $usuario = session('usuario');
        if (!$usuario) throw new Exception('Nenhum usuário autenticado');

        $rules = [
            'cnpj' => ['string', 'size:14', 'required'],
            'tipo' => ['string', 'max:255', 'required']
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


        $sql = "select coletas_nota.notanumero, coletas_nota.notaserie, coletas_nota.notachave, coletas_nota.dhlocal_created_at as dhultimo
        from coletas_nota
        left join coletas_nota_xml on coletas_nota_xml.chave=coletas_nota.notachave
        where coletas_nota.baixanfestatus=2 and coletas_nota.idcoleta is null and coletas_nota_xml.id is null
        and coletas_nota.coletaavulsaignorada=0 and coletas_nota.baixanfetentativas>=2
        and date(coletas_nota.dhlocal_created_at)>=date_add(now(), interval -5 day)
        and coletas_nota.remetentecnpj=?
        order by date(coletas_nota.dhlocal_created_at) asc, coletas_nota.notanumero, coletas_nota.notaserie";

        $dataset = \DB::select( DB::raw($sql), [$request->cnpj]);
        if (!$dataset) throw new Exception('Não existe nenhuma nota pendente inclusão de XML nos últimos 5 dias para o CNPJ ' . $request->cnpj);
        if (count($dataset)<=0) throw new Exception('Não existe nenhuma nota pendente inclusão de XML nos últimos 5 dias para o CNPJ ' . $request->cnpj);

        $tipo = isset($request->tipo) ? $request->tipo : null;
        if (!$tipo) throw new Exception('Nenhuma informação de tipo de requisição');

        $data = isset($request->data) ? $request->data : null;
        if (!$data) throw new Exception('Nenhuma informação de chave ou e-mail');

        if ($tipo === 'email') {
            $to = [];
            foreach ($data['to'] as $key => $value) {
                $email = $value['email'];
                if (validEmail($email)) {
                    $a = [ 'email' => $email ];
                    if (isset($value['nome'])) $a['nome'] = $value['nome'];
                    $to[] = $a;

                }
            }
            if (count($to) === 0) throw new Exception('Nenhum e-mail válido informado');


            $cc = [];
            foreach ($data['cc'] as $key => $value) {
                $email = $value['email'];
                if (validEmail($email)) {
                    $a = [ 'email' => $email ];
                    if (isset($value['nome'])) $a['nome'] = $value['nome'];
                    $cc[] = $a;
                }
            }
            if (count($cc) === 0) $cc = null;
            $mensagem = asset($data['mensagem']) ? $data['mensagem'] : '';
            $assunto = asset($data['assunto']) ? $data['assunto'] : '';
            $chave = $to[0]['email'];
        } else {
            $chave = $data;
        }

        $listanotas = [];
        foreach ($dataset as $row) {
            $listanotas[] = ['numero' => $row->notanumero, 'serie' => $row->notaserie, 'chave' => $row->notachave];
        }

      } catch (\Throwable $th) {
        $ret->msg = $th->getMessage();
        return $ret->toJson();
      }

      try {
        DB::beginTransaction();


        $link = new ColetasNotaXMLToken();
        if ($usuario) $link->usuarioid = $usuario->id;
        $link->origem = '1'; // origem manual
        $link->notas = json_encode($listanotas);
        $link->cnpj = $request->cnpj;
        $link->chave = $chave;
        $link->tipo = $tipo;
        $link->expire_at = Carbon::now()->addHours(12);
        $link->created_at = Carbon::now();
        $codenumber = rand(10000000 , 99999999);
        $link->token = md5($link->created_at->format('Ymdhis') . $link->cnpj . $link->chave . $link->tipo . $codenumber);

        if ($tipo === 'email') {
            $link->to = json_encode($to);
            if ($cc) $link->cc = json_encode($cc);
            $link->assunto = $assunto;
            $link->mensagem = $mensagem;

        }
        $link->save();

        DB::commit();

        $ret->data = [
            'token' => $link->token
        ];
        $ret->ok = true;


        if ($tipo === 'email') $this->dispatch(new EmailTokenXMLPendentePorUsuarioJob($link));

      } catch (\Throwable $th) {
        DB::rollBack();
        $ret->msg = $th->getMessage();
      }
      return $ret->toJson();
    }


}
