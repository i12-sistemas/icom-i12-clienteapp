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

use App\Http\Controllers\RetApiController;

use App\Models\Followup;
use App\Models\FollowupLog;
use App\Models\Cidades;
use App\Models\Cliente;
use App\Models\FollowupErros;
use App\Models\FollowupFiles;

use App\Exports\FollowupListagemExport;
use App\Exports\FollowupLogExport;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\WithHeadings;

class FollowupController extends Controller
{

    private $clientes = null;
    private $errosagendaall = null;
    private $erroscoletaall = null;
    private $errosdtpromessaall = null;

    public function list(Request $request)
    {
      $ret = new RetApiController;
      try {
        $sortby = isset($request->sortby) ? $request->sortby : 'followup.datasolicitacao';
        $descending = isset($request->descending) ? $request->descending : 'asc';

        $id = isset($request->id) ? $request->id : null;
        $dhimportacaoi = isset($request->dhimportacaoi) ? $request->dhimportacaoi : null;
        $dhimportacaof = isset($request->dhimportacaof) ? $request->dhimportacaof : null;

        $datasolicitacaoi = isset($request->datasolicitacaoi) ? $request->datasolicitacaoi : null;
        $datasolicitacaof = isset($request->datasolicitacaof) ? $request->datasolicitacaof : null;

        $aprovacaooci = isset($request->aprovacaooci) ? $request->aprovacaooci : null;
        $aprovacaoocf = isset($request->aprovacaoocf) ? $request->aprovacaoocf : null;

        $dataagendamentocoletai = isset($request->dataagendamentocoletai) ? $request->dataagendamentocoletai : null;
        $dataagendamentocoletaf = isset($request->dataagendamentocoletaf) ? $request->dataagendamentocoletaf : null;

        $dataconfirmacaoi = isset($request->dataconfirmacaoi) ? $request->dataconfirmacaoi : null;
        $dataconfirmacaof = isset($request->dataconfirmacaof) ? $request->dataconfirmacaof : null;

        $datapromessai = isset($request->datapromessai) ? $request->datapromessai : null;
        $datapromessaf = isset($request->datapromessaf) ? $request->datapromessaf : null;

        $datacoletai = isset($request->datacoletai) ? $request->datacoletai : null;
        $datacoletaf = isset($request->datacoletaf) ? $request->datacoletaf : null;

        $dataliberacaoi = isset($request->dataliberacaoi) ? $request->dataliberacaoi : null;
        $dataliberacaof = isset($request->dataliberacaof) ? $request->dataliberacaof : null;

        $datahorafollowupi = isset($request->datahorafollowupi) ? $request->datahorafollowupi : null;
        $datahorafollowupf = isset($request->datahorafollowupf) ? $request->datahorafollowupf : null;

        $vlrunitarioi = isset($request->vlrunitarioi) ? floatVal($request->vlrunitarioi) : null;
        $vlrunitariof = isset($request->vlrunitariof) ? floatVal($request->vlrunitariof) : null;

        $qtdedevidai = isset($request->qtdedevidai) ? floatVal($request->qtdedevidai) : null;
        $qtdedevidaf = isset($request->qtdedevidaf) ? floatVal($request->qtdedevidaf) : null;

        $qtdesolicitadai = isset($request->qtdesolicitadai) ? floatVal($request->qtdesolicitadai) : null;
        $qtdesolicitadaf = isset($request->qtdesolicitadaf) ? floatVal($request->qtdesolicitadaf) : null;

        $qtderecebidai = isset($request->qtderecebidai) ? floatVal($request->qtderecebidai) : null;
        $qtderecebidaf = isset($request->qtderecebidaf) ? floatVal($request->qtderecebidaf) : null;

        $cliente = isset($request->cliente) ? trim($request->cliente) : null;

        $clientefollowupid = isset($request->clientefollowupid) ? utf8_decode(trim(mb_strtoupper($request->clientefollowupid))) : null;
        $updatedusuario = isset($request->updatedusuario) ? utf8_decode(trim(mb_strtoupper($request->updatedusuario))) : null;
        $erroagendastatus = isset($request->erroagendastatus) ? utf8_decode(trim(mb_strtoupper($request->erroagendastatus))) : null;
        $errocoletastatus = isset($request->errocoletastatus) ? utf8_decode(trim(mb_strtoupper($request->errocoletastatus))) : null;
        $errodtpromessastatus = isset($request->errodtpromessastatus) ? utf8_decode(trim(mb_strtoupper($request->errodtpromessastatus))) : null;
        $iniciofollowup = isset($request->iniciofollowup) ? strVal(utf8_decode(trim(mb_strtoupper($request->iniciofollowup)))) : null;
        $statusconfirmacaocoleta = isset($request->statusconfirmacaocoleta) ? strVal(utf8_decode(trim(mb_strtoupper($request->statusconfirmacaocoleta)))) : null;
        $coletaid = isset($request->coletaid) ? intval($request->coletaid) : null;

        $observacao = isset($request->observacao) ? $request->observacao : null;

        $fornecrazao2 = null;
        if (isset($request->fornecrazao2)) {
            $fornecrazao2 = explode(",", $request->fornecrazao2);
            if (!is_array($fornecrazao2)) $fornecrazao2[] = $fornecrazao2;
            $fornecrazao2 = count($fornecrazao2) > 0 ? $fornecrazao2 : null;
        }
        $comprador2 = null;
        if (isset($request->comprador2)) {
            $comprador2 = json_decode($request->comprador2);
            if (!(count($comprador2) > 0)) $comprador2 = null;
        }
        $clientefollowupid2 = null;
        if (isset($request->clientefollowupid2)) {
            $clientefollowupid2 = json_decode($request->clientefollowupid2);
            if (!(count($clientefollowupid2) > 0)) $clientefollowupid2 = null;
        }
        $itemdescricao2 = null;
        if (isset($request->itemdescricao2)) {
            $itemdescricao2 = json_decode($request->itemdescricao2);
            if (!(count($itemdescricao2) > 0)) $itemdescricao2 = null;
        }
        $itemid2 = null;
        if (isset($request->itemid2)) {
            $itemid2 = json_decode($request->itemid2);
            if (!(count($itemid2) > 0)) $itemid2 = null;
        }

        $erroagendastatus2 = null;
        if (isset($request->erroagendastatus2)) {
            $erroagendastatus2 = json_decode($request->erroagendastatus2);
            if (!(count($erroagendastatus2) > 0)) $erroagendastatus2 = null;
        }

        $errocoletastatus2 = null;
        if (isset($request->errocoletastatus2)) {
            $errocoletastatus2 = json_decode($request->errocoletastatus2);
            if (!(count($errocoletastatus2) > 0)) $errocoletastatus2 = null;
        }

        $errodtpromessastatus2 = null;
        if (isset($request->errodtpromessastatus2)) {
            $errodtpromessastatus2 = json_decode($request->errodtpromessastatus2);
            if (!(count($errodtpromessastatus2) > 0)) $errodtpromessastatus2 = null;
        }

        $iniciofollowup2 = null;
        if (isset($request->iniciofollowup2)) {
            $iniciofollowup2 = json_decode($request->iniciofollowup2);
            if (!(count($iniciofollowup2) > 0)) $iniciofollowup2 = null;
        }
        $statusconfirmacaocoleta2 = null;
        if (isset($request->statusconfirmacaocoleta2)) {
            $statusconfirmacaocoleta2 = json_decode($request->statusconfirmacaocoleta2);
            if (!(count($statusconfirmacaocoleta2) > 0)) $statusconfirmacaocoleta2 = null;
        }

        $ordemcompra2 = null;
        if (isset($request->ordemcompra2)) {
            $ordemcompra2 = json_decode($request->ordemcompra2);
            if (!(count($ordemcompra2) > 0)) $ordemcompra2 = null;
        }

        $notafiscal2 = null;
        $notafiscalvazio = false;
        $notafiscalnaovazio = false;
        if (isset($request->notafiscal2)) {
            $list = json_decode($request->notafiscal2);
            $notafiscal2 = [];
            foreach ($list as $value) {
                if ($value === 'vazio') {
                    $notafiscalvazio = true;
                } else if ($value === 'naovazio') {
                    $notafiscalnaovazio = true;
                } else {
                    $notafiscal2[] = $value;
                }
            }
            if (!(count($notafiscal2) > 0)) $notafiscal2 = null;
        }
        $requisicao2 = null;
        if (isset($request->requisicao2)) {
            $requisicao2 = json_decode($request->requisicao2);
            if (!(count($requisicao2) > 0)) $requisicao2 = null;
        }
        $coletaid2 = null;
        if (isset($request->coletaid2)) {
            $coletaid2 = json_decode($request->coletaid2);
            if (!(count($coletaid2) > 0)) $coletaid2 = null;
        }

        $clienterazaosocial = isset($request->clienterazaosocial) ? utf8_decode(trim(mb_strtoupper($request->clienterazaosocial))) : null;

        $fornecrazao = isset($request->fornecrazao) ? utf8_decode(trim(mb_strtoupper($request->fornecrazao))) : null;
        $forneccnpj = isset($request->forneccnpj) ? cleanDocMask(utf8_decode(trim(mb_strtoupper($request->forneccnpj)))) : null;
        $fornectelefone = isset($request->fornectelefone) ? cleanDocMask(utf8_decode(trim(mb_strtoupper($request->fornectelefone)))) : null;
        $email = isset($request->email) ? utf8_decode(trim(mb_strtolower($request->email))) : null;

        $orderby = null;
        if (isset($request->orderby)) {
            $orderby = json_decode($request->orderby,true);
            $orderbynew = [];
            foreach ($orderby as $key => $value) {
                if ($key == 'cliente') {
                    $lKey = 'trim(cliente.fantasia_followup)';
                } else if ($key == 'clientefollowupid') {
                    $lKey = 'trim(cliente.followupid)';
                } else if ($key == 'clientedestino') {
                    $lKey = 'trim(clientedestino.razaosocial)';
                } else if ($key == 'updatedusuario') {
                    $lKey = 'trim(updatedusuario.nome)';
                } else if ($key == 'regiao') {
                    $lKey = 'cidadecoleta.regiaoid';
                } else if ($key == 'enderecocoleta') {
                    $lKey = 'concat(cidadecoleta.cidade,cidadecoleta.uf)';
                } else if ($key == 'cidadedestino') {
                    $lKey = 'concat(cidadedestino.cidade,cidadedestino.uf)';
                } else if ($key == 'datahorafollowup') {
                    $lKey = 'followup.datahora_followup';
                } else {
                    $lKey = 'followup.' . $key;

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
        $query = Followup::select(DB::raw('followup.*'))
                    ->leftJoin('cliente', 'followup.clienteid', '=', 'cliente.id')
                    ->leftJoin('usuario as updatedusuario', 'followup.updated_usuarioid', '=', 'updatedusuario.id')
                    ->leftJoin('followup_erros as erroagenda', 'followup.erroagendaid', '=', 'erroagenda.id')
                    ->leftJoin('followup_erros as errocoleta', 'followup.errocoletaid', '=', 'errocoleta.id')
                    ->leftJoin('followup_erros as errodtpromessa', 'followup.errodtpromessaid', '=', 'errodtpromessa.id')
                    ->with( 'cliente', 'fornecedor', 'erroagenda', 'errocoleta', 'errodtpromessa', 'updated_usuario')
                    ->when(isset($request->erroagendastatus) && ($erroagendastatus ? $erroagendastatus !== '' : false), function ($query) use ($erroagendastatus)  {
                        if ($erroagendastatus == 'OK') {
                            return $query->Where('followup.erroagendastatus', '=', '1');
                        } else if (($erroagendastatus == 'SEM') || ($erroagendastatus == 'SEM STATUS') || ($erroagendastatus == 'SEMSTATUS')) {
                            return $query->Where('followup.erroagendastatus', '=', '0');
                        } else if ($erroagendastatus == 'ERRO') {
                            return $query->Where('followup.erroagendastatus', '=', '2');
                        } else {
                            return $query->Where('followup.erroagendastatus', '=', '2')
                                ->where('erroagenda.descricao', 'like', '%'.$erroagendastatus.'%')
                                ;
                        }
                    })
                    ->when(isset($request->errocoletastatus) && ($errocoletastatus ? $errocoletastatus !== '' : false), function ($query) use ($errocoletastatus)  {
                        if ($errocoletastatus == 'OK') {
                            return $query->Where('followup.errocoletastatus', '=', '1');
                        } else if (($errocoletastatus == 'SEM') || ($errocoletastatus == 'SEM STATUS') || ($errocoletastatus == 'SEMSTATUS')) {
                            return $query->Where('followup.errocoletastatus', '=', '0');
                        } else if ($errocoletastatus == 'ERRO') {
                            return $query->Where('followup.errocoletastatus', '=', '2');
                        } else {
                            return $query->Where('followup.errocoletastatus', '=', '2')
                                ->where('errocoleta.descricao', 'like', '%'.$errocoletastatus.'%')
                                ;
                        }
                    })
                    ->when(isset($request->errodtpromessastatus) && ($errodtpromessastatus ? $errodtpromessastatus !== '' : false), function ($query) use ($errodtpromessastatus)  {
                        if ($errodtpromessastatus == 'OK') {
                            return $query->Where('followup.errodtpromessastatus', '=', '1');
                        } else if (($errodtpromessastatus == 'SEM') || ($errodtpromessastatus == 'SEM STATUS') || ($errodtpromessastatus == 'SEMSTATUS')) {
                            return $query->Where('followup.errodtpromessastatus', '=', '0');
                        } else if ($errodtpromessastatus == 'ERRO') {
                            return $query->Where('followup.errodtpromessastatus', '=', '2');
                        } else {
                            return $query->Where('followup.errodtpromessastatus', '=', '2')
                                ->where('errodtpromessa.descricao', 'like', '%'.$errodtpromessastatus.'%')
                                ;
                        }
                    })

                    ->when(isset($request->iniciofollowup) && ($iniciofollowup !== ''), function ($query) use ($iniciofollowup)  {
                        if (($iniciofollowup == '1') || (str_contains($iniciofollowup, 'CON'))) {
                            return $query->Where('followup.iniciofollowup', '=', '1');
                        } else if (($iniciofollowup == '2') || (str_contains($iniciofollowup, 'FOR'))) {
                            return $query->Where('followup.iniciofollowup', '=', '2');
                        } else if (($iniciofollowup == '0') || (str_contains($iniciofollowup, 'SEM'))) {
                            return $query->Where('followup.iniciofollowup', '=', '0');
                        }
                    })

                    ->when(isset($request->statusconfirmacaocoleta) && ($statusconfirmacaocoleta !== ''), function ($query) use ($statusconfirmacaocoleta)  {
                        if (($statusconfirmacaocoleta == '1') || ($statusconfirmacaocoleta == 'OK')) {
                            return $query->Where('followup.statusconfirmacaocoleta', '=', '1');
                        } else if (($statusconfirmacaocoleta == '2') || ($statusconfirmacaocoleta == 'ERRO')) {
                            return $query->Where('followup.statusconfirmacaocoleta', '=', '2');
                        } else if (($statusconfirmacaocoleta == '0') || (str_contains($statusconfirmacaocoleta, 'SEM'))) {
                            return $query->Where('followup.statusconfirmacaocoleta', '=', '0');
                        }
                    })

                    ->when(isset($request->coletaid) && (intval($coletaid) > 0), function ($query) use ($coletaid)  {
                        return $query->where('followup.coletaid', '=', $coletaid);
                    })
                    ->when(isset($request->clienteid) && (intval($request->clienteid) > 0), function ($query) use ($request)  {
                        return $query->where('followup.clienteid', '=', intval($request->clienteid));
                    })
                    ->when(isset($request->fornecedorid) && (intval($request->fornecedorid) > 0), function ($query) use ($request)  {
                        return $query->where('followup.fornecedorid', '=', intval($request->fornecedorid));
                    })
                    ->when(isset($request->fornecrazao2) && (count($fornecrazao2) > 0), function ($query) use ($fornecrazao2)  {
                        return $query->whereIn('followup.fornecedorid', $fornecrazao2);
                    })
                    ->when(isset($request->comprador2) && (count($comprador2) > 0), function ($query) use ($comprador2)  {
                        return $query->whereIn('followup.comprador', $comprador2);
                    })
                    ->when(isset($request->itemdescricao2) && (count($itemdescricao2) > 0), function ($query) use ($itemdescricao2)  {
                        return $query->whereIn('followup.itemdescricao', $itemdescricao2);
                    })
                    ->when(isset($request->itemid2) && (count($itemid2) > 0), function ($query) use ($itemid2)  {
                        return $query->whereIn('followup.itemid', $itemid2);
                    })

                    ->when(isset($request->erroagendastatus2) && (count($erroagendastatus2) > 0), function ($query) use ($erroagendastatus2)  {
                        $status = [];
                        $erroid = null;
                        if (in_array('semstatus', $erroagendastatus2)) $status[] = '0';
                        if (in_array('ok', $erroagendastatus2)) $status[] = '1';
                        if (in_array('erro', $erroagendastatus2)) {
                            $status[] = '2';
                            $erroid = [];
                            foreach ($erroagendastatus2 as $value) {
                                if (!(($value === 'semstatus') || ($value === 'semstatus') || ($value === 'semstatus'))) {
                                    $n = intVal($value);
                                    if ($n > 0 ) $erroid[]  =$n;
                                }
                            }
                            if (count($erroid) === 0) {
                                $erroid = null;
                            } else {
                                // delete o codigo 2 pq sera somente o erroid
                                if (($key = array_search('2', $status)) !== false) unset($status[$key]);
                            }
                        }
                        return $query->where(function($query2) use ($status, $erroid) {
                            $query2->whereIn('followup.erroagendastatus', $status);
                            if ($erroid) $query2->orWhereIn('followup.erroagendaid', $erroid);
                            return $query2;
                        });
                    })

                    ->when(isset($request->errocoletastatus2) && (count($errocoletastatus2) > 0), function ($query) use ($errocoletastatus2)  {
                        $status = [];
                        $erroid = null;
                        if (in_array('semstatus', $errocoletastatus2)) $status[] = '0';
                        if (in_array('ok', $errocoletastatus2)) $status[] = '1';
                        if (in_array('erro', $errocoletastatus2)) {
                            $status[] = '2';
                            $erroid = [];
                            foreach ($errocoletastatus2 as $value) {
                                if (!(($value === 'semstatus') || ($value === 'semstatus') || ($value === 'semstatus'))) {
                                    $n = intVal($value);
                                    if ($n > 0 ) $erroid[]  =$n;
                                }
                            }
                            if (count($erroid) === 0) {
                                $erroid = null;
                            } else {
                                // delete o codigo 2 pq sera somente o erroid
                                if (($key = array_search('2', $status)) !== false) unset($status[$key]);
                            }
                        }
                        return $query->where(function($query2) use ($status, $erroid) {
                            $query2->whereIn('followup.errocoletastatus', $status);
                            if ($erroid) $query2->orWhereIn('followup.errocoletaid', $erroid);
                            return $query2;
                        });
                    })

                    ->when(isset($request->errodtpromessastatus2) && (count($errodtpromessastatus2) > 0), function ($query) use ($errodtpromessastatus2)  {
                        $status = [];
                        $erroid = null;
                        if (in_array('semstatus', $errodtpromessastatus2)) $status[] = '0';
                        if (in_array('ok', $errodtpromessastatus2)) $status[] = '1';
                        if (in_array('erro', $errodtpromessastatus2)) {
                            $status[] = '2';
                            $erroid = [];
                            foreach ($errodtpromessastatus2 as $value) {
                                if (!(($value === 'semstatus') || ($value === 'semstatus') || ($value === 'semstatus'))) {
                                    $n = intVal($value);
                                    if ($n > 0 ) $erroid[]  =$n;
                                }
                            }
                            if (count($erroid) === 0) {
                                $erroid = null;
                            } else {
                                // delete o codigo 2 pq sera somente o erroid
                                if (($key = array_search('2', $status)) !== false) unset($status[$key]);
                            }
                        }
                        return $query->where(function($query2) use ($status, $erroid) {
                            $query2->whereIn('followup.errodtpromessastatus', $status);
                            if ($erroid) $query2->orWhereIn('followup.errodtpromessaid', $erroid);
                            return $query2;
                        });
                    })

                    ->when(isset($request->iniciofollowup2) && (count($iniciofollowup2) > 0), function ($query) use ($iniciofollowup2)  {
                        return $query->whereIn('followup.iniciofollowup', $iniciofollowup2);
                    })
                    ->when(isset($request->statusconfirmacaocoleta2) && (count($statusconfirmacaocoleta2) > 0), function ($query) use ($statusconfirmacaocoleta2)  {
                        return $query->whereIn('followup.statusconfirmacaocoleta', $statusconfirmacaocoleta2);
                    })

                    ->when(isset($request->ordemcompra2) && (count($ordemcompra2) > 0), function ($query) use ($ordemcompra2)  {
                        return $query->whereIn('followup.ordemcompra', $ordemcompra2);
                    })

                    ->when(isset($request->notafiscal2) && ($notafiscal2 ? count($notafiscal2) > 0 : false) , function ($query) use ($notafiscal2)  {
                        return $query->whereIn('followup.notafiscal', $notafiscal2);
                    })
                    ->when(isset($request->notafiscal2) && ($notafiscalvazio), function ($query) {
                        return $query->whereRaw('ifnull(followup.notafiscal,"") = ""');
                    })
                    ->when(isset($request->notafiscal2) && ($notafiscalnaovazio), function ($query) {
                        return $query->whereRaw('ifnull(followup.notafiscal,"") <> ""');
                    })


                    ->when(isset($request->requisicao2) && (count($requisicao2) > 0), function ($query) use ($requisicao2)  {
                        return $query->whereIn('followup.requisicao', $requisicao2);
                    })
                    ->when(isset($request->coletaid2) && (count($coletaid2) > 0), function ($query) use ($coletaid2)  {
                        return $query->whereIn('followup.coletaid', $coletaid2);
                    })


                    ->when(isset($request->updatedusuario) && ($updatedusuario !== ''), function ($query) use ($updatedusuario)  {
                        return $query->Where('updatedusuario.nome', 'like', '%' . $updatedusuario . '%');
                    })
                    ->when(isset($request->observacao) && ($observacao !== ''), function ($query) use ($observacao)  {
                        return $query->Where('followup.observacao', 'like', '%' . $observacao . '%');
                    })
                    ->when(isset($request->fornecrazao) && ($fornecrazao ? $fornecrazao !== '' : false), function ($query) use ($fornecrazao)  {
                        return $query->Where('followup.fornecrazao', 'like', '%' . $fornecrazao . '%');
                    })
                    ->when(isset($request->forneccnpj) && ($forneccnpj !== ''), function ($query) use ($forneccnpj)  {
                        return $query->Where('followup.forneccnpj', 'like', '%' . $forneccnpj . '%');
                    })
                    ->when(isset($request->fornectelefone) && ($fornectelefone !== ''), function ($query) use ($fornectelefone)  {
                        return $query->Where('followup.fornectelefone', 'like', '%' . $fornectelefone . '%');
                    })
                    ->when(isset($request->email) && ($email !== ''), function ($query) use ($email)  {
                        return $query->Where('followup.email', 'like', '%' . $email . '%');
                    })
                    ->when((isset($request->contato) ? ($request->contato !== '') : false), function ($query) use ($request)  {
                        return $query->Where('followup.contato', 'like', '%' . $request->contato . '%');
                    })
                    ->when((isset($request->forneccidade) ? ($request->forneccidade !== '') : false), function ($query) use ($request)  {
                        return $query->where(function($query2) use ($request) {
                            return $query2->where('followup.forneccidade', 'like', '%'. $request->forneccidade .'%')
                            ->orWhere('followup.fornecuf', 'like', '%'. $request->forneccidade .'%');
                        });
                    })
                    ->when((isset($request->itemid) ? ($request->itemid !== '') : false), function ($query) use ($request)  {
                        return $query->Where('followup.itemid', 'like', '%' . $request->itemid . '%');
                    })
                    ->when((isset($request->itemdescricao) ? ($request->itemdescricao !== '') : false), function ($query) use ($request)  {
                        return $query->Where('followup.itemdescricao', 'like', '%' . $request->itemdescricao . '%');
                    })
                    ->when(isset($request->id), function ($query) use ($id) {
                        return $query->Where('followup.id', '=', $id);
                    })
                    ->when((isset($request->ordemcompra) ? ($request->ordemcompra !== '') : false), function ($query) use ($request)  {
                        return $query->Where('followup.ordemcompra', 'like', '%' . $request->ordemcompra . '%');
                    })
                    ->when((isset($request->ordemcompradig) ? ($request->ordemcompradig !== '') : false), function ($query) use ($request)  {
                        return $query->Where('followup.ordemcompradig', 'like', '%' . $request->ordemcompradig . '%');
                    })
                    ->when((isset($request->notafiscal) ? ($request->notafiscal !== '') : false), function ($query) use ($request)  {
                        return $query->Where('followup.notafiscal', 'like', '%' . $request->notafiscal . '%');
                    })
                    ->when((isset($request->requisicao) ? ($request->requisicao !== '') : false), function ($query) use ($request)  {
                        return $query->Where('followup.requisicao', 'like', '%' . $request->requisicao . '%');
                    })
                    ->when((isset($request->itemnumerolinhapedido) ? ($request->itemnumerolinhapedido !== '') : false), function ($query) use ($request)  {
                        return $query->Where('followup.itemnumerolinhapedido', '=', $request->itemnumerolinhapedido);
                    })
                    ->when((isset($request->comprador) ? ($request->comprador !== '') : false), function ($query) use ($request)  {
                        return $query->Where('followup.comprador', 'like', '%' . $request->comprador . '%');
                    })


                    ->when(isset($request->cliente) && ($cliente != null), function ($query, $t) use ($cliente) {
                        return $query->where('cliente.followupid', 'like', '%'. $cliente .'%');
                    })
                    ->when(isset($request->clientefollowupid2) && (count($clientefollowupid2) > 0), function ($query) use ($clientefollowupid2)  {
                        return $query->whereIn('cliente.followupid', $clientefollowupid2);
                    })
                    ->when(isset($request->clientefollowupid) && ($clientefollowupid != null), function ($query, $t) use ($clientefollowupid) {
                        return $query->Where('cliente.followupid', 'like', '%'. $clientefollowupid .'%');
                    })
                    ->when(isset($request->clienterazaosocial) && ($clienterazaosocial != null), function ($query, $t) use ($clienterazaosocial) {
                        return $query->where(function($query2) use ($clienterazaosocial) {
                            return $query2->where('cliente.razaosocial', 'like', '%'. $clienterazaosocial .'%')
                            ->orWhere('cliente.fantasia', 'like', '%'. $clienterazaosocial .'%')
                            ->orWhere('cliente.fantasia_followup', 'like', '%'. $clienterazaosocial .'%')
                            ->orWhere('cliente.cnpj', 'like', '%'. cleanDocMask($clienterazaosocial) .'%')
                            ->orWhere('cliente.followupid', 'like', '%'. $clienterazaosocial .'%');
                        });
                    })

                    ->when(isset($request->dhimportacaoi), function ($query) use ($dhimportacaoi) {
                        return $query->Where(DB::Raw('date(followup.dhimportacao)'), '>=', $dhimportacaoi);
                    })
                    ->when(isset($request->dhimportacaof), function ($query) use ($dhimportacaof) {
                        return $query->Where(DB::Raw('date(followup.dhimportacao)'), '<=', $dhimportacaof);
                    })

                    ->when(isset($request->datahorafollowupi), function ($query) use ($datahorafollowupi) {
                        return $query->Where(DB::Raw('date(followup.datahora_followup)'), '>=', $datahorafollowupi);
                    })
                    ->when(isset($request->datahorafollowupf), function ($query) use ($datahorafollowupf) {
                        return $query->Where(DB::Raw('date(followup.datahora_followup)'), '<=', $datahorafollowupf);
                    })

                    ->when(isset($request->dataliberacaoi), function ($query) use ($dataliberacaoi) {
                        return $query->Where(DB::Raw('date(followup.dataliberacao)'), '>=', $dataliberacaoi);
                    })
                    ->when(isset($request->dataliberacaof), function ($query) use ($dataliberacaof) {
                        return $query->Where(DB::Raw('date(followup.dataliberacao)'), '<=', $dataliberacaof);
                    })

                    ->when(isset($request->datasolicitacaoi), function ($query) use ($datasolicitacaoi) {
                        return $query->Where(DB::Raw('date(followup.datasolicitacao)'), '>=', $datasolicitacaoi);
                    })
                    ->when(isset($request->datasolicitacaof), function ($query) use ($datasolicitacaof) {
                        return $query->Where(DB::Raw('date(followup.datasolicitacao)'), '<=', $datasolicitacaof);
                    })

                    ->when(isset($request->aprovacaooci), function ($query) use ($aprovacaooci) {
                        return $query->Where(DB::Raw('date(followup.aprovacaooc)'), '>=', $aprovacaooci);
                    })
                    ->when(isset($request->aprovacaoocf), function ($query) use ($aprovacaoocf) {
                        return $query->Where(DB::Raw('date(followup.aprovacaooc)'), '<=', $aprovacaoocf);
                    })

                    ->when(isset($request->dataconfirmacaoi), function ($query) use ($dataconfirmacaoi) {
                        return $query->Where(DB::Raw('date(followup.dataconfirmacao)'), '>=', $dataconfirmacaoi);
                    })
                    ->when(isset($request->dataconfirmacaof), function ($query) use ($dataconfirmacaof) {
                        return $query->Where(DB::Raw('date(followup.dataconfirmacao)'), '<=', $dataconfirmacaof);
                    })

                    ->when(isset($request->datacoletai), function ($query) use ($datacoletai) {
                        return $query->Where(DB::Raw('date(followup.datacoleta)'), '>=', $datacoletai);
                    })
                    ->when(isset($request->datacoletaf), function ($query) use ($datacoletaf) {
                        return $query->Where(DB::Raw('date(followup.datacoleta)'), '<=', $datacoletaf);
                    })

                    ->when(isset($request->dataagendamentocoletai), function ($query) use ($dataagendamentocoletai) {
                        return $query->Where(DB::Raw('date(followup.dataagendamentocoleta)'), '>=', $dataagendamentocoletai);
                    })
                    ->when(isset($request->dataagendamentocoletaf), function ($query) use ($dataagendamentocoletaf) {
                        return $query->Where(DB::Raw('date(followup.dataagendamentocoleta)'), '<=', $dataagendamentocoletaf);
                    })

                    ->when(isset($request->datapromessai), function ($query) use ($datapromessai) {
                        return $query->Where(DB::Raw('date(followup.datapromessa)'), '>=', $datapromessai);
                    })
                    ->when(isset($request->datapromessaf), function ($query) use ($datapromessaf) {
                        return $query->Where(DB::Raw('date(followup.datapromessa)'), '<=', $datapromessaf);
                    })

                    ->when(isset($request->vlrunitarioi), function ($query) use ($vlrunitarioi) {
                        return $query->Where(DB::Raw('followup.vlrunitario'), '>=', $vlrunitarioi);
                    })
                    ->when(isset($request->vlrunitariof), function ($query) use ($vlrunitariof) {
                        return $query->Where(DB::Raw('followup.vlrunitario'), '<=', $vlrunitariof);
                    })

                    ->when(isset($request->qtdedevidai), function ($query) use ($qtdedevidai) {
                        return $query->Where(DB::Raw('followup.qtdedevida'), '>=', $qtdedevidai);
                    })
                    ->when(isset($request->qtdedevidaf), function ($query) use ($qtdedevidaf) {
                        return $query->Where(DB::Raw('followup.qtdedevida'), '<=', $qtdedevidaf);
                    })

                    ->when(isset($request->qtdesolicitadai), function ($query) use ($qtdesolicitadai) {
                        return $query->Where(DB::Raw('followup.qtdesolicitada'), '>=', $qtdesolicitadai);
                    })
                    ->when(isset($request->qtdesolicitadaf), function ($query) use ($qtdesolicitadaf) {
                        return $query->Where(DB::Raw('followup.qtdesolicitada'), '<=', $qtdesolicitadaf);
                    })

                    ->when(isset($request->qtderecebidai), function ($query) use ($qtderecebidai) {
                        return $query->Where(DB::Raw('followup.qtderecebida'), '>=', $qtderecebidai);
                    })
                    ->when(isset($request->qtderecebidaf), function ($query) use ($qtderecebidaf) {
                        return $query->Where(DB::Raw('followup.qtderecebida'), '<=', $qtderecebidaf);
                    })

                    ->when($request->orderby, function ($query) use ($orderby) {
                        foreach ($orderby as $key => $value) {
                            $query->orderByRaw($key  . ' ' . $value);
                        }
                        return $query;
                    })
                    ;

        $dataset = $query->paginate($perpage);

        $dados = [];
        foreach ($dataset as $row) {
            $dados[] = $row->export(false);
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


    public function print_listagem (Request $request)
    {
        $ret = new RetApiController;
        try {
            $disk = Storage::disk('public');
            $usuario = session('usuario');
            $output = mb_strtolower(isset($request->output) ? $request->output : 'pdf');
            if (!(($output === 'csv') || ($output === 'xlsx'))) $output = 'xlsx';

            $visiblecolumns = null;
            if (isset($request->visiblecolumns)) {
                $visiblecolumns = explode(",", $request->visiblecolumns);
                if (!is_array($visiblecolumns)) $visiblecolumns[] = $visiblecolumns;
                $visiblecolumns = count($visiblecolumns) > 0 ? $visiblecolumns : null;
            }

            // copia do list
            $sortby = isset($request->sortby) ? $request->sortby : 'followup.datasolicitacao';
        $descending = isset($request->descending) ? $request->descending : 'asc';

        $id = isset($request->id) ? $request->id : null;
        $dhimportacaoi = isset($request->dhimportacaoi) ? $request->dhimportacaoi : null;
        $dhimportacaof = isset($request->dhimportacaof) ? $request->dhimportacaof : null;

        $datasolicitacaoi = isset($request->datasolicitacaoi) ? $request->datasolicitacaoi : null;
        $datasolicitacaof = isset($request->datasolicitacaof) ? $request->datasolicitacaof : null;

        $aprovacaooci = isset($request->aprovacaooci) ? $request->aprovacaooci : null;
        $aprovacaoocf = isset($request->aprovacaoocf) ? $request->aprovacaoocf : null;

        $dataagendamentocoletai = isset($request->dataagendamentocoletai) ? $request->dataagendamentocoletai : null;
        $dataagendamentocoletaf = isset($request->dataagendamentocoletaf) ? $request->dataagendamentocoletaf : null;

        $dataconfirmacaoi = isset($request->dataconfirmacaoi) ? $request->dataconfirmacaoi : null;
        $dataconfirmacaof = isset($request->dataconfirmacaof) ? $request->dataconfirmacaof : null;

        $datapromessai = isset($request->datapromessai) ? $request->datapromessai : null;
        $datapromessaf = isset($request->datapromessaf) ? $request->datapromessaf : null;

        $datacoletai = isset($request->datacoletai) ? $request->datacoletai : null;
        $datacoletaf = isset($request->datacoletaf) ? $request->datacoletaf : null;

        $dataliberacaoi = isset($request->dataliberacaoi) ? $request->dataliberacaoi : null;
        $dataliberacaof = isset($request->dataliberacaof) ? $request->dataliberacaof : null;

        $datahorafollowupi = isset($request->datahorafollowupi) ? $request->datahorafollowupi : null;
        $datahorafollowupf = isset($request->datahorafollowupf) ? $request->datahorafollowupf : null;

        $vlrunitarioi = isset($request->vlrunitarioi) ? floatVal($request->vlrunitarioi) : null;
        $vlrunitariof = isset($request->vlrunitariof) ? floatVal($request->vlrunitariof) : null;

        $qtdedevidai = isset($request->qtdedevidai) ? floatVal($request->qtdedevidai) : null;
        $qtdedevidaf = isset($request->qtdedevidaf) ? floatVal($request->qtdedevidaf) : null;

        $qtdesolicitadai = isset($request->qtdesolicitadai) ? floatVal($request->qtdesolicitadai) : null;
        $qtdesolicitadaf = isset($request->qtdesolicitadaf) ? floatVal($request->qtdesolicitadaf) : null;

        $qtderecebidai = isset($request->qtderecebidai) ? floatVal($request->qtderecebidai) : null;
        $qtderecebidaf = isset($request->qtderecebidaf) ? floatVal($request->qtderecebidaf) : null;

        $cliente = isset($request->cliente) ? trim($request->cliente) : null;

        $clientefollowupid = isset($request->clientefollowupid) ? utf8_decode(trim(mb_strtoupper($request->clientefollowupid))) : null;
        $updatedusuario = isset($request->updatedusuario) ? utf8_decode(trim(mb_strtoupper($request->updatedusuario))) : null;
        $erroagendastatus = isset($request->erroagendastatus) ? utf8_decode(trim(mb_strtoupper($request->erroagendastatus))) : null;
        $errocoletastatus = isset($request->errocoletastatus) ? utf8_decode(trim(mb_strtoupper($request->errocoletastatus))) : null;
        $errodtpromessastatus = isset($request->errodtpromessastatus) ? utf8_decode(trim(mb_strtoupper($request->errodtpromessastatus))) : null;
        $iniciofollowup = isset($request->iniciofollowup) ? strVal(utf8_decode(trim(mb_strtoupper($request->iniciofollowup)))) : null;
        $statusconfirmacaocoleta = isset($request->statusconfirmacaocoleta) ? strVal(utf8_decode(trim(mb_strtoupper($request->statusconfirmacaocoleta)))) : null;
        $coletaid = isset($request->coletaid) ? intval($request->coletaid) : null;

        $observacao = isset($request->observacao) ? $request->observacao : null;

        $fornecrazao2 = null;
        if (isset($request->fornecrazao2)) {
            $fornecrazao2 = explode(",", $request->fornecrazao2);
            if (!is_array($fornecrazao2)) $fornecrazao2[] = $fornecrazao2;
            $fornecrazao2 = count($fornecrazao2) > 0 ? $fornecrazao2 : null;
        }
        $comprador2 = null;
        if (isset($request->comprador2)) {
            $comprador2 = json_decode($request->comprador2);
            if (!(count($comprador2) > 0)) $comprador2 = null;
        }
        $clientefollowupid2 = null;
        if (isset($request->clientefollowupid2)) {
            $clientefollowupid2 = json_decode($request->clientefollowupid2);
            if (!(count($clientefollowupid2) > 0)) $clientefollowupid2 = null;
        }
        $itemdescricao2 = null;
        if (isset($request->itemdescricao2)) {
            $itemdescricao2 = json_decode($request->itemdescricao2);
            if (!(count($itemdescricao2) > 0)) $itemdescricao2 = null;
        }
        $itemid2 = null;
        if (isset($request->itemid2)) {
            $itemid2 = json_decode($request->itemid2);
            if (!(count($itemid2) > 0)) $itemid2 = null;
        }

        $erroagendastatus2 = null;
        if (isset($request->erroagendastatus2)) {
            $erroagendastatus2 = json_decode($request->erroagendastatus2);
            if (!(count($erroagendastatus2) > 0)) $erroagendastatus2 = null;
        }

        $errocoletastatus2 = null;
        if (isset($request->errocoletastatus2)) {
            $errocoletastatus2 = json_decode($request->errocoletastatus2);
            if (!(count($errocoletastatus2) > 0)) $errocoletastatus2 = null;
        }

        $errodtpromessastatus2 = null;
        if (isset($request->errodtpromessastatus2)) {
            $errodtpromessastatus2 = json_decode($request->errodtpromessastatus2);
            if (!(count($errodtpromessastatus2) > 0)) $errodtpromessastatus2 = null;
        }

        $iniciofollowup2 = null;
        if (isset($request->iniciofollowup2)) {
            $iniciofollowup2 = json_decode($request->iniciofollowup2);
            if (!(count($iniciofollowup2) > 0)) $iniciofollowup2 = null;
        }
        $statusconfirmacaocoleta2 = null;
        if (isset($request->statusconfirmacaocoleta2)) {
            $statusconfirmacaocoleta2 = json_decode($request->statusconfirmacaocoleta2);
            if (!(count($statusconfirmacaocoleta2) > 0)) $statusconfirmacaocoleta2 = null;
        }

        $ordemcompra2 = null;
        if (isset($request->ordemcompra2)) {
            $ordemcompra2 = json_decode($request->ordemcompra2);
            if (!(count($ordemcompra2) > 0)) $ordemcompra2 = null;
        }

        $notafiscal2 = null;
        $notafiscalvazio = false;
        $notafiscalnaovazio = false;
        if (isset($request->notafiscal2)) {
            $list = json_decode($request->notafiscal2);
            $notafiscal2 = [];
            foreach ($list as $value) {
                if ($value === 'vazio') {
                    $notafiscalvazio = true;
                } else if ($value === 'naovazio') {
                    $notafiscalnaovazio = true;
                } else {
                    $notafiscal2[] = $value;
                }
            }
            if (!(count($notafiscal2) > 0)) $notafiscal2 = null;
        }
        $requisicao2 = null;
        if (isset($request->requisicao2)) {
            $requisicao2 = json_decode($request->requisicao2);
            if (!(count($requisicao2) > 0)) $requisicao2 = null;
        }
        $coletaid2 = null;
        if (isset($request->coletaid2)) {
            $coletaid2 = json_decode($request->coletaid2);
            if (!(count($coletaid2) > 0)) $coletaid2 = null;
        }

        $clienterazaosocial = isset($request->clienterazaosocial) ? utf8_decode(trim(mb_strtoupper($request->clienterazaosocial))) : null;

        $fornecrazao = isset($request->fornecrazao) ? utf8_decode(trim(mb_strtoupper($request->fornecrazao))) : null;
        $forneccnpj = isset($request->forneccnpj) ? cleanDocMask(utf8_decode(trim(mb_strtoupper($request->forneccnpj)))) : null;
        $fornectelefone = isset($request->fornectelefone) ? cleanDocMask(utf8_decode(trim(mb_strtoupper($request->fornectelefone)))) : null;
        $email = isset($request->email) ? utf8_decode(trim(mb_strtolower($request->email))) : null;

        $orderby = null;
        if (isset($request->orderby)) {
            $orderby = json_decode($request->orderby,true);
            $orderbynew = [];
            foreach ($orderby as $key => $value) {
                if ($key == 'cliente') {
                    $lKey = 'trim(cliente.fantasia_followup)';
                } else if ($key == 'clientefollowupid') {
                    $lKey = 'trim(cliente.followupid)';
                } else if ($key == 'clientedestino') {
                    $lKey = 'trim(clientedestino.razaosocial)';
                } else if ($key == 'updatedusuario') {
                    $lKey = 'trim(updatedusuario.nome)';
                } else if ($key == 'regiao') {
                    $lKey = 'cidadecoleta.regiaoid';
                } else if ($key == 'enderecocoleta') {
                    $lKey = 'concat(cidadecoleta.cidade,cidadecoleta.uf)';
                } else if ($key == 'cidadedestino') {
                    $lKey = 'concat(cidadedestino.cidade,cidadedestino.uf)';
                } else if ($key == 'datahorafollowup') {
                    $lKey = 'followup.datahora_followup';
                } else {
                    $lKey = 'followup.' . $key;

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
        $query = Followup::select(DB::raw('followup.*'))
                    ->leftJoin('cliente', 'followup.clienteid', '=', 'cliente.id')
                    ->leftJoin('usuario as updatedusuario', 'followup.updated_usuarioid', '=', 'updatedusuario.id')
                    ->leftJoin('followup_erros as erroagenda', 'followup.erroagendaid', '=', 'erroagenda.id')
                    ->leftJoin('followup_erros as errocoleta', 'followup.errocoletaid', '=', 'errocoleta.id')
                    ->leftJoin('followup_erros as errodtpromessa', 'followup.errodtpromessaid', '=', 'errodtpromessa.id')
                    ->with( 'cliente', 'fornecedor', 'erroagenda', 'errocoleta', 'errodtpromessa', 'updated_usuario')
                    ->when(isset($request->erroagendastatus) && ($erroagendastatus ? $erroagendastatus !== '' : false), function ($query) use ($erroagendastatus)  {
                        if ($erroagendastatus == 'OK') {
                            return $query->Where('followup.erroagendastatus', '=', '1');
                        } else if (($erroagendastatus == 'SEM') || ($erroagendastatus == 'SEM STATUS') || ($erroagendastatus == 'SEMSTATUS')) {
                            return $query->Where('followup.erroagendastatus', '=', '0');
                        } else if ($erroagendastatus == 'ERRO') {
                            return $query->Where('followup.erroagendastatus', '=', '2');
                        } else {
                            return $query->Where('followup.erroagendastatus', '=', '2')
                                ->where('erroagenda.descricao', 'like', '%'.$erroagendastatus.'%')
                                ;
                        }
                    })
                    ->when(isset($request->errocoletastatus) && ($errocoletastatus ? $errocoletastatus !== '' : false), function ($query) use ($errocoletastatus)  {
                        if ($errocoletastatus == 'OK') {
                            return $query->Where('followup.errocoletastatus', '=', '1');
                        } else if (($errocoletastatus == 'SEM') || ($errocoletastatus == 'SEM STATUS') || ($errocoletastatus == 'SEMSTATUS')) {
                            return $query->Where('followup.errocoletastatus', '=', '0');
                        } else if ($errocoletastatus == 'ERRO') {
                            return $query->Where('followup.errocoletastatus', '=', '2');
                        } else {
                            return $query->Where('followup.errocoletastatus', '=', '2')
                                ->where('errocoleta.descricao', 'like', '%'.$errocoletastatus.'%')
                                ;
                        }
                    })
                    ->when(isset($request->errodtpromessastatus) && ($errodtpromessastatus ? $errodtpromessastatus !== '' : false), function ($query) use ($errodtpromessastatus)  {
                        if ($errodtpromessastatus == 'OK') {
                            return $query->Where('followup.errodtpromessastatus', '=', '1');
                        } else if (($errodtpromessastatus == 'SEM') || ($errodtpromessastatus == 'SEM STATUS') || ($errodtpromessastatus == 'SEMSTATUS')) {
                            return $query->Where('followup.errodtpromessastatus', '=', '0');
                        } else if ($errodtpromessastatus == 'ERRO') {
                            return $query->Where('followup.errodtpromessastatus', '=', '2');
                        } else {
                            return $query->Where('followup.errodtpromessastatus', '=', '2')
                                ->where('errodtpromessa.descricao', 'like', '%'.$errodtpromessastatus.'%')
                                ;
                        }
                    })

                    ->when(isset($request->iniciofollowup) && ($iniciofollowup !== ''), function ($query) use ($iniciofollowup)  {
                        if (($iniciofollowup == '1') || (str_contains($iniciofollowup, 'CON'))) {
                            return $query->Where('followup.iniciofollowup', '=', '1');
                        } else if (($iniciofollowup == '2') || (str_contains($iniciofollowup, 'FOR'))) {
                            return $query->Where('followup.iniciofollowup', '=', '2');
                        } else if (($iniciofollowup == '0') || (str_contains($iniciofollowup, 'SEM'))) {
                            return $query->Where('followup.iniciofollowup', '=', '0');
                        }
                    })

                    ->when(isset($request->statusconfirmacaocoleta) && ($statusconfirmacaocoleta !== ''), function ($query) use ($statusconfirmacaocoleta)  {
                        if (($statusconfirmacaocoleta == '1') || ($statusconfirmacaocoleta == 'OK')) {
                            return $query->Where('followup.statusconfirmacaocoleta', '=', '1');
                        } else if (($statusconfirmacaocoleta == '2') || ($statusconfirmacaocoleta == 'ERRO')) {
                            return $query->Where('followup.statusconfirmacaocoleta', '=', '2');
                        } else if (($statusconfirmacaocoleta == '0') || (str_contains($statusconfirmacaocoleta, 'SEM'))) {
                            return $query->Where('followup.statusconfirmacaocoleta', '=', '0');
                        }
                    })

                    ->when(isset($request->coletaid) && (intval($coletaid) > 0), function ($query) use ($coletaid)  {
                        return $query->where('followup.coletaid', '=', $coletaid);
                    })
                    ->when(isset($request->clienteid) && (intval($request->clienteid) > 0), function ($query) use ($request)  {
                        return $query->where('followup.clienteid', '=', intval($request->clienteid));
                    })
                    ->when(isset($request->fornecedorid) && (intval($request->fornecedorid) > 0), function ($query) use ($request)  {
                        return $query->where('followup.fornecedorid', '=', intval($request->fornecedorid));
                    })
                    ->when(isset($request->fornecrazao2) && (count($fornecrazao2) > 0), function ($query) use ($fornecrazao2)  {
                        return $query->whereIn('followup.fornecedorid', $fornecrazao2);
                    })
                    ->when(isset($request->comprador2) && (count($comprador2) > 0), function ($query) use ($comprador2)  {
                        return $query->whereIn('followup.comprador', $comprador2);
                    })
                    ->when(isset($request->itemdescricao2) && (count($itemdescricao2) > 0), function ($query) use ($itemdescricao2)  {
                        return $query->whereIn('followup.itemdescricao', $itemdescricao2);
                    })
                    ->when(isset($request->itemid2) && (count($itemid2) > 0), function ($query) use ($itemid2)  {
                        return $query->whereIn('followup.itemid', $itemid2);
                    })

                    ->when(isset($request->erroagendastatus2) && (count($erroagendastatus2) > 0), function ($query) use ($erroagendastatus2)  {
                        $status = [];
                        $erroid = null;
                        if (in_array('semstatus', $erroagendastatus2)) $status[] = '0';
                        if (in_array('ok', $erroagendastatus2)) $status[] = '1';
                        if (in_array('erro', $erroagendastatus2)) {
                            $status[] = '2';
                            $erroid = [];
                            foreach ($erroagendastatus2 as $value) {
                                if (!(($value === 'semstatus') || ($value === 'semstatus') || ($value === 'semstatus'))) {
                                    $n = intVal($value);
                                    if ($n > 0 ) $erroid[]  =$n;
                                }
                            }
                            if (count($erroid) === 0) {
                                $erroid = null;
                            } else {
                                // delete o codigo 2 pq sera somente o erroid
                                if (($key = array_search('2', $status)) !== false) unset($status[$key]);
                            }
                        }
                        return $query->where(function($query2) use ($status, $erroid) {
                            $query2->whereIn('followup.erroagendastatus', $status);
                            if ($erroid) $query2->orWhereIn('followup.erroagendaid', $erroid);
                            return $query2;
                        });
                    })

                    ->when(isset($request->errocoletastatus2) && (count($errocoletastatus2) > 0), function ($query) use ($errocoletastatus2)  {
                        $status = [];
                        $erroid = null;
                        if (in_array('semstatus', $errocoletastatus2)) $status[] = '0';
                        if (in_array('ok', $errocoletastatus2)) $status[] = '1';
                        if (in_array('erro', $errocoletastatus2)) {
                            $status[] = '2';
                            $erroid = [];
                            foreach ($errocoletastatus2 as $value) {
                                if (!(($value === 'semstatus') || ($value === 'semstatus') || ($value === 'semstatus'))) {
                                    $n = intVal($value);
                                    if ($n > 0 ) $erroid[]  =$n;
                                }
                            }
                            if (count($erroid) === 0) {
                                $erroid = null;
                            } else {
                                // delete o codigo 2 pq sera somente o erroid
                                if (($key = array_search('2', $status)) !== false) unset($status[$key]);
                            }
                        }
                        return $query->where(function($query2) use ($status, $erroid) {
                            $query2->whereIn('followup.errocoletastatus', $status);
                            if ($erroid) $query2->orWhereIn('followup.errocoletaid', $erroid);
                            return $query2;
                        });
                    })

                    ->when(isset($request->errodtpromessastatus2) && (count($errodtpromessastatus2) > 0), function ($query) use ($errodtpromessastatus2)  {
                        $status = [];
                        $erroid = null;
                        if (in_array('semstatus', $errodtpromessastatus2)) $status[] = '0';
                        if (in_array('ok', $errodtpromessastatus2)) $status[] = '1';
                        if (in_array('erro', $errodtpromessastatus2)) {
                            $status[] = '2';
                            $erroid = [];
                            foreach ($errodtpromessastatus2 as $value) {
                                if (!(($value === 'semstatus') || ($value === 'semstatus') || ($value === 'semstatus'))) {
                                    $n = intVal($value);
                                    if ($n > 0 ) $erroid[]  =$n;
                                }
                            }
                            if (count($erroid) === 0) {
                                $erroid = null;
                            } else {
                                // delete o codigo 2 pq sera somente o erroid
                                if (($key = array_search('2', $status)) !== false) unset($status[$key]);
                            }
                        }
                        return $query->where(function($query2) use ($status, $erroid) {
                            $query2->whereIn('followup.errodtpromessastatus', $status);
                            if ($erroid) $query2->orWhereIn('followup.errodtpromessaid', $erroid);
                            return $query2;
                        });
                    })

                    ->when(isset($request->iniciofollowup2) && (count($iniciofollowup2) > 0), function ($query) use ($iniciofollowup2)  {
                        return $query->whereIn('followup.iniciofollowup', $iniciofollowup2);
                    })
                    ->when(isset($request->statusconfirmacaocoleta2) && (count($statusconfirmacaocoleta2) > 0), function ($query) use ($statusconfirmacaocoleta2)  {
                        return $query->whereIn('followup.statusconfirmacaocoleta', $statusconfirmacaocoleta2);
                    })

                    ->when(isset($request->ordemcompra2) && (count($ordemcompra2) > 0), function ($query) use ($ordemcompra2)  {
                        return $query->whereIn('followup.ordemcompra', $ordemcompra2);
                    })

                    ->when(isset($request->notafiscal2) && ($notafiscal2 ? count($notafiscal2) > 0 : false) , function ($query) use ($notafiscal2)  {
                        return $query->whereIn('followup.notafiscal', $notafiscal2);
                    })
                    ->when(isset($request->notafiscal2) && ($notafiscalvazio), function ($query) {
                        return $query->whereRaw('ifnull(followup.notafiscal,"") = ""');
                    })
                    ->when(isset($request->notafiscal2) && ($notafiscalnaovazio), function ($query) {
                        return $query->whereRaw('ifnull(followup.notafiscal,"") <> ""');
                    })


                    ->when(isset($request->requisicao2) && (count($requisicao2) > 0), function ($query) use ($requisicao2)  {
                        return $query->whereIn('followup.requisicao', $requisicao2);
                    })
                    ->when(isset($request->coletaid2) && (count($coletaid2) > 0), function ($query) use ($coletaid2)  {
                        return $query->whereIn('followup.coletaid', $coletaid2);
                    })


                    ->when(isset($request->updatedusuario) && ($updatedusuario !== ''), function ($query) use ($updatedusuario)  {
                        return $query->Where('updatedusuario.nome', 'like', '%' . $updatedusuario . '%');
                    })
                    ->when(isset($request->observacao) && ($observacao !== ''), function ($query) use ($observacao)  {
                        return $query->Where('followup.observacao', 'like', '%' . $observacao . '%');
                    })
                    ->when(isset($request->fornecrazao) && ($fornecrazao ? $fornecrazao !== '' : false), function ($query) use ($fornecrazao)  {
                        return $query->Where('followup.fornecrazao', 'like', '%' . $fornecrazao . '%');
                    })
                    ->when(isset($request->forneccnpj) && ($forneccnpj !== ''), function ($query) use ($forneccnpj)  {
                        return $query->Where('followup.forneccnpj', 'like', '%' . $forneccnpj . '%');
                    })
                    ->when(isset($request->fornectelefone) && ($fornectelefone !== ''), function ($query) use ($fornectelefone)  {
                        return $query->Where('followup.fornectelefone', 'like', '%' . $fornectelefone . '%');
                    })
                    ->when(isset($request->email) && ($email !== ''), function ($query) use ($email)  {
                        return $query->Where('followup.email', 'like', '%' . $email . '%');
                    })
                    ->when((isset($request->contato) ? ($request->contato !== '') : false), function ($query) use ($request)  {
                        return $query->Where('followup.contato', 'like', '%' . $request->contato . '%');
                    })
                    ->when((isset($request->forneccidade) ? ($request->forneccidade !== '') : false), function ($query) use ($request)  {
                        return $query->where(function($query2) use ($request) {
                            return $query2->where('followup.forneccidade', 'like', '%'. $request->forneccidade .'%')
                            ->orWhere('followup.fornecuf', 'like', '%'. $request->forneccidade .'%');
                        });
                    })
                    ->when((isset($request->itemid) ? ($request->itemid !== '') : false), function ($query) use ($request)  {
                        return $query->Where('followup.itemid', 'like', '%' . $request->itemid . '%');
                    })
                    ->when((isset($request->itemdescricao) ? ($request->itemdescricao !== '') : false), function ($query) use ($request)  {
                        return $query->Where('followup.itemdescricao', 'like', '%' . $request->itemdescricao . '%');
                    })
                    ->when(isset($request->id), function ($query) use ($id) {
                        return $query->Where('followup.id', '=', $id);
                    })
                    ->when((isset($request->ordemcompra) ? ($request->ordemcompra !== '') : false), function ($query) use ($request)  {
                        return $query->Where('followup.ordemcompra', 'like', '%' . $request->ordemcompra . '%');
                    })
                    ->when((isset($request->ordemcompradig) ? ($request->ordemcompradig !== '') : false), function ($query) use ($request)  {
                        return $query->Where('followup.ordemcompradig', 'like', '%' . $request->ordemcompradig . '%');
                    })
                    ->when((isset($request->notafiscal) ? ($request->notafiscal !== '') : false), function ($query) use ($request)  {
                        return $query->Where('followup.notafiscal', 'like', '%' . $request->notafiscal . '%');
                    })
                    ->when((isset($request->requisicao) ? ($request->requisicao !== '') : false), function ($query) use ($request)  {
                        return $query->Where('followup.requisicao', 'like', '%' . $request->requisicao . '%');
                    })
                    ->when((isset($request->itemnumerolinhapedido) ? ($request->itemnumerolinhapedido !== '') : false), function ($query) use ($request)  {
                        return $query->Where('followup.itemnumerolinhapedido', '=', $request->itemnumerolinhapedido);
                    })
                    ->when((isset($request->comprador) ? ($request->comprador !== '') : false), function ($query) use ($request)  {
                        return $query->Where('followup.comprador', 'like', '%' . $request->comprador . '%');
                    })


                    ->when(isset($request->cliente) && ($cliente != null), function ($query, $t) use ($cliente) {
                        return $query->where('cliente.followupid', 'like', '%'. $cliente .'%');
                    })
                    ->when(isset($request->clientefollowupid2) && (count($clientefollowupid2) > 0), function ($query) use ($clientefollowupid2)  {
                        return $query->whereIn('cliente.followupid', $clientefollowupid2);
                    })
                    ->when(isset($request->clientefollowupid) && ($clientefollowupid != null), function ($query, $t) use ($clientefollowupid) {
                        return $query->Where('cliente.followupid', 'like', '%'. $clientefollowupid .'%');
                    })
                    ->when(isset($request->clienterazaosocial) && ($clienterazaosocial != null), function ($query, $t) use ($clienterazaosocial) {
                        return $query->where(function($query2) use ($clienterazaosocial) {
                            return $query2->where('cliente.razaosocial', 'like', '%'. $clienterazaosocial .'%')
                            ->orWhere('cliente.fantasia', 'like', '%'. $clienterazaosocial .'%')
                            ->orWhere('cliente.fantasia_followup', 'like', '%'. $clienterazaosocial .'%')
                            ->orWhere('cliente.cnpj', 'like', '%'. cleanDocMask($clienterazaosocial) .'%')
                            ->orWhere('cliente.followupid', 'like', '%'. $clienterazaosocial .'%');
                        });
                    })

                    ->when(isset($request->dhimportacaoi), function ($query) use ($dhimportacaoi) {
                        return $query->Where(DB::Raw('date(followup.dhimportacao)'), '>=', $dhimportacaoi);
                    })
                    ->when(isset($request->dhimportacaof), function ($query) use ($dhimportacaof) {
                        return $query->Where(DB::Raw('date(followup.dhimportacao)'), '<=', $dhimportacaof);
                    })

                    ->when(isset($request->datahorafollowupi), function ($query) use ($datahorafollowupi) {
                        return $query->Where(DB::Raw('date(followup.datahora_followup)'), '>=', $datahorafollowupi);
                    })
                    ->when(isset($request->datahorafollowupf), function ($query) use ($datahorafollowupf) {
                        return $query->Where(DB::Raw('date(followup.datahora_followup)'), '<=', $datahorafollowupf);
                    })

                    ->when(isset($request->dataliberacaoi), function ($query) use ($dataliberacaoi) {
                        return $query->Where(DB::Raw('date(followup.dataliberacao)'), '>=', $dataliberacaoi);
                    })
                    ->when(isset($request->dataliberacaof), function ($query) use ($dataliberacaof) {
                        return $query->Where(DB::Raw('date(followup.dataliberacao)'), '<=', $dataliberacaof);
                    })

                    ->when(isset($request->datasolicitacaoi), function ($query) use ($datasolicitacaoi) {
                        return $query->Where(DB::Raw('date(followup.datasolicitacao)'), '>=', $datasolicitacaoi);
                    })
                    ->when(isset($request->datasolicitacaof), function ($query) use ($datasolicitacaof) {
                        return $query->Where(DB::Raw('date(followup.datasolicitacao)'), '<=', $datasolicitacaof);
                    })

                    ->when(isset($request->aprovacaooci), function ($query) use ($aprovacaooci) {
                        return $query->Where(DB::Raw('date(followup.aprovacaooc)'), '>=', $aprovacaooci);
                    })
                    ->when(isset($request->aprovacaoocf), function ($query) use ($aprovacaoocf) {
                        return $query->Where(DB::Raw('date(followup.aprovacaooc)'), '<=', $aprovacaoocf);
                    })

                    ->when(isset($request->dataconfirmacaoi), function ($query) use ($dataconfirmacaoi) {
                        return $query->Where(DB::Raw('date(followup.dataconfirmacao)'), '>=', $dataconfirmacaoi);
                    })
                    ->when(isset($request->dataconfirmacaof), function ($query) use ($dataconfirmacaof) {
                        return $query->Where(DB::Raw('date(followup.dataconfirmacao)'), '<=', $dataconfirmacaof);
                    })

                    ->when(isset($request->datacoletai), function ($query) use ($datacoletai) {
                        return $query->Where(DB::Raw('date(followup.datacoleta)'), '>=', $datacoletai);
                    })
                    ->when(isset($request->datacoletaf), function ($query) use ($datacoletaf) {
                        return $query->Where(DB::Raw('date(followup.datacoleta)'), '<=', $datacoletaf);
                    })

                    ->when(isset($request->dataagendamentocoletai), function ($query) use ($dataagendamentocoletai) {
                        return $query->Where(DB::Raw('date(followup.dataagendamentocoleta)'), '>=', $dataagendamentocoletai);
                    })
                    ->when(isset($request->dataagendamentocoletaf), function ($query) use ($dataagendamentocoletaf) {
                        return $query->Where(DB::Raw('date(followup.dataagendamentocoleta)'), '<=', $dataagendamentocoletaf);
                    })

                    ->when(isset($request->datapromessai), function ($query) use ($datapromessai) {
                        return $query->Where(DB::Raw('date(followup.datapromessa)'), '>=', $datapromessai);
                    })
                    ->when(isset($request->datapromessaf), function ($query) use ($datapromessaf) {
                        return $query->Where(DB::Raw('date(followup.datapromessa)'), '<=', $datapromessaf);
                    })

                    ->when(isset($request->vlrunitarioi), function ($query) use ($vlrunitarioi) {
                        return $query->Where(DB::Raw('followup.vlrunitario'), '>=', $vlrunitarioi);
                    })
                    ->when(isset($request->vlrunitariof), function ($query) use ($vlrunitariof) {
                        return $query->Where(DB::Raw('followup.vlrunitario'), '<=', $vlrunitariof);
                    })

                    ->when(isset($request->qtdedevidai), function ($query) use ($qtdedevidai) {
                        return $query->Where(DB::Raw('followup.qtdedevida'), '>=', $qtdedevidai);
                    })
                    ->when(isset($request->qtdedevidaf), function ($query) use ($qtdedevidaf) {
                        return $query->Where(DB::Raw('followup.qtdedevida'), '<=', $qtdedevidaf);
                    })

                    ->when(isset($request->qtdesolicitadai), function ($query) use ($qtdesolicitadai) {
                        return $query->Where(DB::Raw('followup.qtdesolicitada'), '>=', $qtdesolicitadai);
                    })
                    ->when(isset($request->qtdesolicitadaf), function ($query) use ($qtdesolicitadaf) {
                        return $query->Where(DB::Raw('followup.qtdesolicitada'), '<=', $qtdesolicitadaf);
                    })

                    ->when(isset($request->qtderecebidai), function ($query) use ($qtderecebidai) {
                        return $query->Where(DB::Raw('followup.qtderecebida'), '>=', $qtderecebidai);
                    })
                    ->when(isset($request->qtderecebidaf), function ($query) use ($qtderecebidaf) {
                        return $query->Where(DB::Raw('followup.qtderecebida'), '<=', $qtderecebidaf);
                    })

                    ->when($request->orderby, function ($query) use ($orderby) {
                        foreach ($orderby as $key => $value) {
                            $query->orderByRaw($key  . ' ' . $value);
                        }
                        return $query;
                    })
                    ;
            // copia do list

            $count = $query->count();
            if ($count > 20000) throw new Exception('Limite de 20.000 registros foi excedido. Informe os filtros para limitar a consulta.');

            $rows = $query->get();
            if (!$rows) throw new Exception('Nenhum registro encontrado');
            if (count($rows) == 0) throw new Exception('Nenhum registro encontrado');
            if (count($rows) > 20000) throw new Exception('Limite de 20.000 registros foi excedido. Informe os filtros para limitar a consulta.');

                // impressão indisponivel
            if ($output == 'pdf') {
                throw new Exception('Versão em PDF indisponível');
            } else {
                $ret->msg = self::exportFile($rows, $output, $visiblecolumns);
                $ret->ok = true;
            }
            return $ret->toJson();
        } catch (\Throwable $th) {
            $ret->msg = $th->getMessage();
            return $ret->toJson();
        }
    }

    public function exportFile($dataset, $format, $visiblecolumns) {
        try {
            $format = mb_strtolower($format);
            if (!(($format=='xlsx') || ($format=='csv') || ($format=='xls')))
                throw new Exception('Formato inválido. Permitido somente XLSX, XLS, CSV');

            $path = 'export/' . Carbon::now()->format('Y-m-d') . '/';
            $filename = 'fup-listagem-' . Carbon::now()->format('Y-m-d-H-i-s-') . md5(createRandomVal(5) . Carbon::now()) . '.' . $format;
            $fullfilename = '';
            ini_set('memory_limit', '-1');
            $export = new FollowupListagemExport($dataset, $visiblecolumns);
            $fullfilename =  $path . $filename;

            $formatExport = \Maatwebsite\Excel\Excel::XLSX;
            switch ($format) {
                case 'csv':
                    $formatExport = \Maatwebsite\Excel\Excel::CSV;
                    break;

                case 'xls':
                    $formatExport = \Maatwebsite\Excel\Excel::XLS;
                    break;

                default:
                    $formatExport = \Maatwebsite\Excel\Excel::XLSX;
                    break;
            }

            Excel::store($export, $fullfilename, 'public', $formatExport);

            $disk = Storage::disk('public');
            if (!$disk->exists($fullfilename)) throw new Exception('Nenhum arquivo encontrado no disco');

            return $disk->url($fullfilename);

        } catch (\Throwable $th) {
            throw new Exception('Erro ao gerar arquivo - ' . $th->getMessage());
        }
    }


    public function list_compradores(Request $request)
    {
      $ret = new RetApiController;
      try {
        $dataset = Followup::select('comprador')
                    ->groupBy('comprador')
                    ->orderBy('comprador', 'asc')
                    ->get();
        $dados = [];
        foreach ($dataset as $row) {
            $dados[] = $row->comprador;
        }
        $ret->data = $dados;
        $ret->ok = true;
      } catch (\Throwable $th) {
        $ret->msg = $th->getMessage();
      }
      return $ret->toJson();
    }

    public function list_clientesfupid(Request $request)
    {
      $ret = new RetApiController;
      try {
        $dataset = Followup::select('cliente.followupid')
                    ->join('cliente', 'followup.clienteid', '=', 'cliente.id')
                    ->whereRaw('not(ifnull(cliente.followupid,"")="")')
                    ->groupBy('cliente.followupid')
                    ->orderBy('cliente.followupid', 'asc')
                    ->get();
        $dados = [];
        foreach ($dataset as $row) {
            $dados[] = $row->followupid;
        }
        $ret->data = $dados;
        $ret->ok = true;
      } catch (\Throwable $th) {
        $ret->msg = $th->getMessage();
      }
      return $ret->toJson();
    }


    public function list_itemdescricao(Request $request)
    {
      $ret = new RetApiController;
      try {
        $find = isset($request->find) ? utf8_decode($request->find) : null;
        $ids = null;
        if (isset($request->ids)) {
            $ids = json_decode($request->ids);
            if (!(count($ids) > 0)) $ids = null;
        }
        $perpage = isset($request->perpage) ? $request->perpage : 25;
        $dataset = Followup::select('itemdescricao')
                ->when(isset($request->find) && ($find !== ''), function ($query) use ($find) {
                    return $query->Where('itemdescricao', 'like', '%'.$find.'%');
                })
                ->when(isset($request->ids) && ($ids), function ($query) use ($ids) {
                    return $query->WhereIn('itemdescricao', $ids);
                })
                ->groupBy('itemdescricao')
                ->orderBy('itemdescricao', 'asc')
                ->paginate($perpage);
        $dados = [];
        foreach ($dataset as $row) {
            $dados[] = [
                'itemdescricao' => $row->itemdescricao,
            ];
        }
        $ret->data = $dados;
        $ret->collection = $dataset;
        $ret->ok = true;
      } catch (\Throwable $th) {
        $ret->msg = $th->getMessage();
      }
      return $ret->toJson();
    }

    public function list_itemid(Request $request)
    {
      $ret = new RetApiController;
      try {
        $find = isset($request->find) ? utf8_decode($request->find) : null;
        $ids = null;
        if (isset($request->ids)) {
            $ids = json_decode($request->ids);
            if (!(count($ids) > 0)) $ids = null;
        }
        $perpage = isset($request->perpage) ? $request->perpage : 25;
        $dataset = Followup::select('itemid')
                ->when(isset($request->find) && ($find !== ''), function ($query) use ($find) {
                    return $query->Where('itemid', 'like', '%'.$find.'%');
                })
                ->when(isset($request->ids) && ($ids), function ($query) use ($ids) {
                    return $query->WhereIn('itemid', $ids);
                })
                ->groupBy('itemid')
                ->orderBy('itemid', 'asc')
                ->paginate($perpage);
        $dados = [];
        foreach ($dataset as $row) {
            $dados[] = [
                'itemid' => $row->itemid,
            ];
        }
        $ret->data = $dados;
        $ret->collection = $dataset;
        $ret->ok = true;
      } catch (\Throwable $th) {
        $ret->msg = $th->getMessage();
      }
      return $ret->toJson();
    }

    public function log(Request $request, $id)
    {
      $ret = new RetApiController;
      try {
        // $usuario = session('usuario');
        // if (!$usuario) throw new Exception('Nenhum usuário autenticado');
        $output = $request->has('output') ? $request->output : 'json';
        $output = mb_strtolower($output);
        if (!(($output=='xlsx') || ($output=='csv') || ($output=='xls'))) $output = 'json';

        if (!$id) throw new Exception('Nenhum ID informado');
        if (!(intVal($id) > 0)) throw new Exception('ID inválido');

        $fup = Followup::find($id);
        if (!$fup) throw new Exception('Nenhum registro de FollowUp encontrado');

        $perpage = isset($request->perpage) ? $request->perpage : 25;
        $dataset = FollowupLog::select(DB::raw('followup_log.*'))
                    ->with('erroagenda', 'errocoleta', 'errodtpromessa')
                    ->where('followupid', '=', $id)
                    ->orderBy('created_at', 'desc')
                    ->orderBy('id', 'desc')
                    ->paginate($perpage);


        // impressão indisponivel
        if ((($output=='xlsx') || ($output=='csv') || ($output=='xls'))) {
            $ret->msg = self::logExportExcel($dataset, $output, $id);
            $ret->ok = true;
        } else {
            $dados = [];
            foreach ($dataset as $row) {
                $dados[] = $row->export(false);
            }
            $ret->data = $dados;
            $ret->collection = $dataset;
            $ret->ok = true;
        }
      } catch (\Throwable $th) {
        $ret->msg = $th->getMessage();
      }
      return $ret->toJson();
    }

    public function logExportExcel($dataset, $format, $id)
    {
        try {
            $format = mb_strtolower($format);
            if (!(($format=='xlsx') || ($format=='csv') || ($format=='xls')))
                throw new Exception('Formato inválido. Permitido somente XLSX, XLS, CSV');

            $path = 'export/' . Carbon::now()->format('Y-m-d') . '/';
            $filename = 'fup-id-' . $id . '-log-' . Carbon::now()->format('Y-m-d-H-i-s-') . md5(createRandomVal(5) . Carbon::now()) . '.' . $format;
            $fullfilename = '';
            ini_set('memory_limit', '-1');
            $export = new FollowupLogExport($dataset);
            $fullfilename =  $path . $filename;

            $formatExport = \Maatwebsite\Excel\Excel::XLSX;
            switch ($format) {
                case 'csv':
                    $formatExport = \Maatwebsite\Excel\Excel::CSV;
                    break;

                case 'xls':
                    $formatExport = \Maatwebsite\Excel\Excel::XLS;
                    break;

                default:
                    $formatExport = \Maatwebsite\Excel\Excel::XLSX;
                    break;
            }

            Excel::store($export, $fullfilename, 'public', $formatExport);

            $disk = Storage::disk('public');
            if (!$disk->exists($fullfilename)) throw new Exception('Nenhum arquivo encontrado no disco');

            return $disk->url($fullfilename);

        } catch (\Throwable $th) {
            throw new Exception('Erro ao gerar arquivo - ' . $th->getMessage());
        }
    }



    //update em massa
    public function savemass(Request $request)
    {
        $ret = new RetApiController;
        try {
            $usuario = session('usuario');
            if (!$usuario) throw new Exception('Nenhum usuário autenticado');

            $data = isset($request->data) ? $request->data : null;
            if (!$data) throw new Exception('Nenhum dados informado');
            if (!is_array($data)) throw new Exception('Dados informados fora do padrão');

            $ids = [];
            foreach ($data as $row) {
                $ids[] = $row['id'];
            }
            $dataset = Followup::whereIn('id', $ids)->get();
            if (!$dataset) throw new Exception("Nenhum registro encontrado");

            // foreach ($coletas as $coleta) {
            //     if (!in_array($coleta->situacao, [ColetasSituacaoType::tcsBloqueado, ColetasSituacaoType::tcsLiberado])) {
            //         throw new Exception("Situação atual da coleta " . $coleta->id . " não permite alteração - Situação: " . $coleta->situacao . " - " . ColetasSituacaoType::getDescription($coleta->situacao));
            //     }
            // }

        } catch (\Throwable $th) {
            $ret->msg = $th->getMessage();
            return $ret->toJson();
        }

        try {

            DB::beginTransaction();


            $updated = [];
            $error = [];
            foreach ($data as $row) {
                try {
                    $fup = $dataset->firstWhere('id', $row['id']);
                    if (!$fup) throw new Exception('FollowUp ID não encontrado com o id ' . $row['id']);

                    foreach ($row as $key => $value) {
                        $fup[$key] = $value;
                    }
                    $fup->datahora_followup = Carbon::now();
                    $fup->updated_usuarioid = $usuario->id;
                    $fup->save();

                    try {
                        $logresult = $fup->registerLog(1, $usuario->id, false); //1=Alteração manual operador, 2=Novo registro importação planilha, 3=update registro importação planilha
                    } catch (\Throwable $th) {
                        \Log::error('RegisterLog :: ' . $th->getMessage());
                    }


                    $updated[] = $fup->id;

                } catch (\Throwable $th) {
                    $error[] = [
                        'id' => $row['id'],
                        'erro' => $th->getMessage()
                    ];
                }
            }


            DB::commit();

            $data = [
                'updated' => $updated
            ];
            if (count($error) > 0) $data['error'] = $error;

            $ret->msg = count($updated) . ' registros atualizados';
            $ret->data = $data;
            $ret->ok = true;

        } catch (\Throwable $th) {
            DB::rollBack();
            $ret->msg = $th->getMessage();
        }

        return $ret->toJson();
    }



    public function dashboard1(Request $request)
    {
      $ret = new RetApiController;
      try {

          try {
            $dti = isset($request->dti) ? $request->dti : null;
            $dtf = isset($request->dtf) ? $request->dtf : null;
            if ((!$dti) || (!$dtf)) {
                $maxdh = Followup::max(\DB::raw('DATE(dhimportacao)'));
                if (!$maxdh) throw new Exception('Nenhuma data informada e nenhuma data localizada atualmente');
                $dti = Carbon::createFromFormat('Y-m-d', $maxdh);
                $dtf = Carbon::createFromFormat('Y-m-d', $maxdh);
            } else {
                $dti = Carbon::createFromFormat('Y-m-d', $dti);
                $dtf = Carbon::createFromFormat('Y-m-d', $dtf);
            }
        } catch (\Throwable $th) {
            throw new Exception('Data inicial e/ou final :: ' . $th->getMessage());
        }

        $dados = [
            'periodo' => [
                'dti' => $dti->format('Y-m-d'),
                'dtf' => $dtf->format('Y-m-d'),
            ]
        ];

        // var ocsAberto = from m in ctx.followup where m.data_hora_importacao >= dt_ini && m.data_hora_importacao < dt_fim select new { abc = m.ordem_compra + "-" + m.ordem_compra_dig };
        $OCemAberto = Followup::whereBetween(\DB::raw('DATE(dhimportacao)'), [$dti->format('Y-m-d'),  $dtf->format('Y-m-d')])
                                ->count(DB::raw('distinct concat(ordemcompra, ifnull(ordemcompradig,""))'));

        // var linhasAbertoVencidas = ctx.followup.Where(c => c.data_hora_importacao >= dt_ini && c.data_hora_importacao < dt_fim && c.data_promessa < dt_ini).Count();
        $linhaemaberto = Followup::whereBetween(\DB::raw('DATE(dhimportacao)'), [$dti->format('Y-m-d'),  $dtf->format('Y-m-d')])
                                ->count(DB::raw('distinct id'));

        // var linhasAbertoVencidas = ctx.followup.Where(c => c.data_hora_importacao >= dt_ini && c.data_hora_importacao < dt_fim && c.data_promessa < dt_ini).Count();
        $linhaemabertovencidas = Followup::whereBetween(\DB::raw('DATE(dhimportacao)'), [$dti->format('Y-m-d'),  $dtf->format('Y-m-d')])
                                ->where(\DB::raw('DATE(datapromessa)'), '<', $dti->format('Y-m-d'))
                                ->count(DB::raw('distinct id'));

        // linhasAbertoIndicador.Value = ((float)linhasAbertoVencidas / (float)linhasAberto) * 100;
        $linhasAbertoIndicador = ($linhaemabertovencidas / $linhaemaberto) * 100;



        try {
            // var saldoAberto = ctx.followup.Where(c => c.data_hora_importacao >= dt_ini && c.data_hora_importacao < dt_fim).Sum(f => (f.qtdade_devida * f.vlr_unitario));
            $saldoAberto = Followup::whereBetween(\DB::raw('DATE(dhimportacao)'), [$dti->format('Y-m-d'),  $dtf->format('Y-m-d')])
                                    ->sum(DB::raw('((ifnull(qtdedevida,0) * ifnull(vlrunitario,0)))'));
            if(!$saldoAberto) $saldoAberto = 0;
        } catch (\Throwable $th) {
            throw new Exception('Falha ao consulta saldo em aberto :: ' . $th->getMessage());
        }

        try {
            // var saldoAbertoVencidas = ctx.followup.Where(c => c.data_hora_importacao >= dt_ini && c.data_hora_importacao < dt_fim && c.data_promessa < dt_ini).Sum(f => f.total_linha_oc);
            $saldoAbertoVencidas = Followup::whereBetween(\DB::raw('DATE(dhimportacao)'), [$dti->format('Y-m-d'),  $dtf->format('Y-m-d')])
                                    ->where(\DB::raw('DATE(datapromessa)'), '<', $dti->format('Y-m-d'))
                                    ->sum(\DB::raw('ifnull(totallinhaoc,0)'));
            if(!$saldoAbertoVencidas) $saldoAbertoVencidas = 0;

            // saldoAbertoIndicador.Value = ((float)saldoAbertoVencidas / (float)saldoAberto) * 100;
            $saldoAbertoIndicador = round(($saldoAbertoVencidas / $saldoAberto) * 100, 3);
        } catch (\Throwable $th) {
            throw new Exception('Falha ao consulta saldo em aberto vencido :: ' . $th->getMessage());
        }

        try {
            // -- var linhasAbertoAcompanhadas = ctx.followup.Where(c => c.data_hora_importacao >= dt_ini && c.data_hora_importacao < dt_fim && c.data_hora_followup != null).Count();
            $linhasAbertoAcompanhadas = Followup::whereBetween(\DB::raw('DATE(dhimportacao)'), [$dti->format('Y-m-d'),  $dtf->format('Y-m-d')])
                                ->whereNotNull('datahora_followup')
                                ->count();
            if (!$linhasAbertoAcompanhadas) $linhasAbertoAcompanhadas = 0;
            if ($linhaemaberto == 0) $linhasAbertoAcompanhadas = 0;
        } catch (\Throwable $th) {
            throw new Exception('Falha ao consulta linhas acompanhadas :: ' . $th->getMessage());
        }


        try {
            // (((float)linhasAbertoAcompanhadas / (float)linhasAberto) * 100);
            $emAcompanhamentoPerc = round(($linhasAbertoAcompanhadas / $linhaemaberto) * 100, 3);
            $emAbertoPerc = 100 - $emAcompanhamentoPerc ;
            $aAcompanhamentoPerc = [
                'emacompanhamentoperc' => $emAcompanhamentoPerc,
                'emabertoperc' => $emAbertoPerc,
                'emacompanhamento' => $linhasAbertoAcompanhadas
            ];
        } catch (\Throwable $th) {
            throw new Exception('Falha ao consulta linhas acompanhadas :: ' . $th->getMessage());
        }



        try {

            // var linhasAbertoAcompanhadasComErro = ctx.followup.Where(c => c.data_hora_importacao >= dt_ini && c.data_hora_importacao < dt_fim &&
            //             c.data_hora_followup != null &&
            //             (c.id_followup_erro_agendamento != null ||
            //             c.id_followup_erro_coleta != null ||
            //             c.id_followup_erro_dt_promessa != null)
            //             ).Count();
            $linhasAbertoComErro = Followup::whereBetween(\DB::raw('DATE(dhimportacao)'), [$dti->format('Y-m-d'),  $dtf->format('Y-m-d')])
                                ->whereNotNull('datahora_followup')
                                ->where(function($query) {
                                    return $query->where('erroagendastatus', '=', '2')
                                    ->orWhere('errocoletastatus', '=', '2')
                                    ->orWhere('errodtpromessastatus', '=', '2');
                                })
                                ->count();
            if (!$linhasAbertoComErro) $linhasAbertoComErro = 0;

            //(((float)linhasAbertoAcompanhadasComErro / (float)linhasAbertoAcompanhadas) * 100);
            $comErroPerc = round(($linhasAbertoComErro / $linhasAbertoAcompanhadas) * 100, 3);
            $semErroPerc = 100 - $comErroPerc ;
            $errosPerc = [
                'errosqtde' => $linhasAbertoComErro,
                'errosperc' => $comErroPerc,
                'okperc' => $semErroPerc
            ];

        } catch (\Throwable $th) {
            throw new Exception('Falha ao consulta linhas acompanhadas :: ' . $th->getMessage());
        }


        $dados['totalizadores'] = [
            'ocemaberto' => $OCemAberto,
            'linhaemaberto' => $linhaemaberto,
            'linhaemabertovencidas' => $linhaemabertovencidas,
            'linhaemabertoindicador' => round($linhasAbertoIndicador, 2),
            'linhasemabertoacompanhadas' => $linhasAbertoAcompanhadas,
            'saldoaberto' => round($saldoAberto, 2),
            'saldoabertovencidas' => round($saldoAbertoVencidas, 2),
            'saldoabertoindicador' => $saldoAbertoIndicador,
            'emabertopercentual' => $aAcompanhamentoPerc,
            'statusprocessopercentual' => $errosPerc,
        ];


        try {
            $di = $dti->format('Y-m-d');
            $df = $dtf->format('Y-m-d');
            $sql = "select total, tipo, descricao
            from (
                (
                    select count(distinct f.id) as total, 'A' as tipo, e.descricao
                    from followup f
                    inner join followup_erros as e on f.erroagendaid = e.id
                    where date(dhimportacao) between date('$di') and  date('$df')
                    and f.erroagendastatus='2'
                    group by e.id
                )
                UNION ALL
                (
                    select count(distinct f.id) as total, 'C' as tipo, e.descricao
                    from followup f
                    inner join followup_erros as e on f.errocoletaid = e.id
                    where date(dhimportacao) between date('$di') and  date('$df')
                    and f.errocoletastatus='2'
                    group by e.id
                )
                UNION ALL
                (
                    select count(distinct f.id) as total, 'D' as tipo, e.descricao
                    from followup f
                    inner join followup_erros as e on f.errodtpromessaid = e.id
                    where date(dhimportacao) between date('$di') and  date('$df')
                    and f.errodtpromessastatus='2'
                    group by e.id
                )
            ) as dados
            order by total desc";
            $dataset = \DB::select(\DB::raw($sql));
            $total = 0;
            foreach ($dataset as $r) {
                $total = $total + $r->total;
            }

            $erros = [];
            foreach ($dataset as $r) {
                $erros[] = [
                    'total' => $r->total,
                    'descricao' => utf8_encode($r->descricao),
                    'tipo' => $r->tipo,
                    'indice' => round((($r->total/$total)*100),5),
                ];
            }
            $dados['erroslista'] = $erros;


        } catch (\Throwable $th) {
            throw new Exception('Falha ao consulta linhas com erros :: ' . $th->getMessage());
        }

        try {
            $di = $dti->format('Y-m-d');
            $df = $dtf->format('Y-m-d');
            $sql = "select dados.*, ((linhasAbertoVencidas / linhasAberto)*100) as linhasAbertoVencidasIndice, ((saldoAbertoVencidas / saldoAberto) * 100) as saldoAbertoVencidasIndice
                from (
                    (
                        select  'até 30 dias' as label,
                        count(distinct id) as linhasAberto, round(sum(totallinhaoc),6) as saldoAberto,
                        count(distinct if(date(datapromessa) < date('$di'), id, null)) as linhasAbertoVencidas, round(sum(if(date(datapromessa) < date('$di'), totallinhaoc, 0)),6) as saldoAbertoVencidas
                        from followup
                        where
                        if(ucase(tipooc)='Blanket', datediff('$di', date(dataliberacao)),  datediff('$di', date(aprovacaooc))) <= 30
                        and date(dhimportacao) between date('$di') and  date('$df')
                    )
                    UNION ALL
                    (
                        select  'de 31 a 60 dias' as label,
                        count(distinct id) as linhasAberto, round(sum(totallinhaoc),6) as saldoAberto,
                        count(distinct if(date(datapromessa) < date('$di'), id, null)) as linhasAbertoVencidas, round(sum(if(date(datapromessa) < date('$di'), totallinhaoc, 0)),6) as saldoAbertoVencidas
                        from followup
                        where
                        if(ucase(tipooc)='Blanket', datediff('$di', date(dataliberacao)),  datediff('$di', date(aprovacaooc))) between 31 AND 60
                        and date(dhimportacao) between date('$di') and  date('$df')
                    )
                    UNION ALL
                    (
                        select  'de 61 a 90 dias' as label,
                        count(distinct id) as linhasAberto, round(sum(totallinhaoc),6) as saldoAberto,
                        count(distinct if(date(datapromessa) < date('$di'), id, null)) as linhasAbertoVencidas, round(sum(if(date(datapromessa) < date('$di'), totallinhaoc, 0)),6) as saldoAbertoVencidas
                        from followup
                        where
                        if(ucase(tipooc)='Blanket', datediff('$di', date(dataliberacao)),  datediff('$di', date(aprovacaooc))) between 61 AND 90
                        and date(dhimportacao) between date('$di') and  date('$df')
                    )
                    UNION ALL
                    (
                        select  'de 91 a 180 dias' as label,
                        count(distinct id) as linhasAberto, round(sum(totallinhaoc),6) as saldoAberto,
                        count(distinct if(date(datapromessa) < date('$di'), id, null)) as linhasAbertoVencidas, round(sum(if(date(datapromessa) < date('$di'), totallinhaoc, 0)),6) as saldoAbertoVencidas
                        from followup
                        where
                        if(ucase(tipooc)='Blanket', datediff('$di', date(dataliberacao)),  datediff('$di', date(aprovacaooc))) between 91 AND 180
                        and date(dhimportacao) between date('$di') and  date('$df')
                    )
                    UNION ALL
                    (
                        select  'acima de 180 dias' as label,
                        count(distinct id) as linhasAberto, round(sum(totallinhaoc),6) as saldoAberto,
                        count(distinct if(date(datapromessa) < date('$di'), id, null)) as linhasAbertoVencidas, round(sum(if(date(datapromessa) < date('$di'), totallinhaoc, 0)),6) as saldoAbertoVencidas
                        from followup
                        where
                        if(ucase(tipooc)='Blanket', datediff('$di', date(dataliberacao)),  datediff('$di', date(aprovacaooc))) > 180
                        and date(dhimportacao) between date('$di') and  date('$df')
                    )
                )  as dados";
            $dataset = \DB::select(\DB::raw($sql));
            $historicolista = [];
            foreach ($dataset as $r) {
                $historicolista[] = [
                    'label' => $r->label,
                    'linhasaberto' => $r->linhasaberto,
                    'saldoaberto' => $r->saldoaberto,
                    'linhasabertovencidas' => $r->linhasabertovencidas,
                    'saldoabertovencidas' => $r->saldoabertovencidas,
                    'linhasabertovencidasindice' => $r->linhasabertovencidasindice,
                    'saldoabertovencidasindice' => $r->saldoabertovencidasindice
                ];
            };
            $dados['historicolista'] = $historicolista;

        } catch (\Throwable $th) {
            throw new Exception('Falha ao consulta histórico :: ' . $th->getMessage());
        }





        $ret->data = $dados;
        $ret->ok = true;
      } catch (\Throwable $th) {
        $ret->msg = $th->getMessage();
      }
      return $ret->toJson();
    }

}
