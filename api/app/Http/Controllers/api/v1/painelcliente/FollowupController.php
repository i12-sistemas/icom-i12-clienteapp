<?php

namespace App\Http\Controllers\api\v1\painelcliente;

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


class FollowupController extends Controller
{


  public function dashboard1(Request $request)
    {
      $ret = new RetApiController;
      try {
        $usuario = session('usuario');
        $auth = session('auth');
        if (!$usuario) throw new Exception("Nenhum usuário identificado");
        if (!($usuario->id>0)) throw new Exception("Nenhum usuário identificado");

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
                            ->where('clienteid', '=', $usuario->clienteid)
                            ->count(DB::raw('distinct concat(ordemcompra, ifnull(ordemcompradig,""))'));

      // var linhasAbertoVencidas = ctx.followup.Where(c => c.data_hora_importacao >= dt_ini && c.data_hora_importacao < dt_fim && c.data_promessa < dt_ini).Count();
      $linhaemaberto = Followup::whereBetween(\DB::raw('DATE(dhimportacao)'), [$dti->format('Y-m-d'),  $dtf->format('Y-m-d')])
                    ->where('clienteid', '=', $usuario->clienteid)
                    ->count(DB::raw('distinct id'));

      // var linhasAbertoVencidas = ctx.followup.Where(c => c.data_hora_importacao >= dt_ini && c.data_hora_importacao < dt_fim && c.data_promessa < dt_ini).Count();
      $linhaemabertovencidas = Followup::whereBetween(\DB::raw('DATE(dhimportacao)'), [$dti->format('Y-m-d'),  $dtf->format('Y-m-d')])
                            ->where('clienteid', '=', $usuario->clienteid)
                            ->where(\DB::raw('DATE(datapromessa)'), '<', $dti->format('Y-m-d'))
                            ->count(DB::raw('distinct id'));

      // linhasAbertoIndicador.Value = ((float)linhasAbertoVencidas / (float)linhasAberto) * 100;
      $linhasAbertoIndicador = $linhaemaberto==0 ? 0 : (($linhaemabertovencidas / $linhaemaberto) * 100);



      try {
          // var saldoAberto = ctx.followup.Where(c => c.data_hora_importacao >= dt_ini && c.data_hora_importacao < dt_fim).Sum(f => (f.qtdade_devida * f.vlr_unitario));
          $saldoAberto = Followup::whereBetween(\DB::raw('DATE(dhimportacao)'), [$dti->format('Y-m-d'),  $dtf->format('Y-m-d')])
                                ->where('clienteid', '=', $usuario->clienteid)
                                ->sum(DB::raw('((ifnull(qtdedevida,0) * ifnull(vlrunitario,0)))'));
          if(!$saldoAberto) $saldoAberto = 0;
      } catch (\Throwable $th) {
          throw new Exception('Falha ao consulta saldo em aberto :: ' . $th->getMessage());
      }

      try {
          // var saldoAbertoVencidas = ctx.followup.Where(c => c.data_hora_importacao >= dt_ini && c.data_hora_importacao < dt_fim && c.data_promessa < dt_ini).Sum(f => f.total_linha_oc);
          $saldoAbertoVencidas = Followup::whereBetween(\DB::raw('DATE(dhimportacao)'), [$dti->format('Y-m-d'),  $dtf->format('Y-m-d')])
                                  ->where(\DB::raw('DATE(datapromessa)'), '<', $dti->format('Y-m-d'))
                                  ->where('clienteid', '=', $usuario->clienteid)
                                  ->sum(\DB::raw('ifnull(totallinhaoc,0)'));
          if(!$saldoAbertoVencidas) $saldoAbertoVencidas = 0;

          // saldoAbertoIndicador.Value = ((float)saldoAbertoVencidas / (float)saldoAberto) * 100;
          $saldoAbertoIndicador = $saldoAberto==0 ? 0 : (round(($saldoAbertoVencidas / $saldoAberto) * 100, 3));
      } catch (\Throwable $th) {
          throw new Exception('Falha ao consulta saldo em aberto vencido :: ' . $th->getMessage());
      }

      try {
          // -- var linhasAbertoAcompanhadas = ctx.followup.Where(c => c.data_hora_importacao >= dt_ini && c.data_hora_importacao < dt_fim && c.data_hora_followup != null).Count();
          $linhasAbertoAcompanhadas = Followup::whereBetween(\DB::raw('DATE(dhimportacao)'), [$dti->format('Y-m-d'),  $dtf->format('Y-m-d')])
                              ->whereNotNull('datahora_followup')
                              ->where('clienteid', '=', $usuario->clienteid)
                              ->count();
          if (!$linhasAbertoAcompanhadas) $linhasAbertoAcompanhadas = 0;
          if ($linhaemaberto == 0) $linhasAbertoAcompanhadas = 0;
      } catch (\Throwable $th) {
          throw new Exception('Falha ao consulta linhas acompanhadas :: ' . $th->getMessage());
      }


      try {
          // (((float)linhasAbertoAcompanhadas / (float)linhasAberto) * 100);
          $emAcompanhamentoPerc = $linhaemaberto==0 ? 0 : round(($linhasAbertoAcompanhadas / $linhaemaberto) * 100, 3);
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
                              ->where('clienteid', '=', $usuario->clienteid)
                              ->where(function($query) {
                                  return $query->where('erroagendastatus', '=', '2')
                                  ->orWhere('errocoletastatus', '=', '2')
                                  ->orWhere('errodtpromessastatus', '=', '2');
                              })
                              ->count();
          if (!$linhasAbertoComErro) $linhasAbertoComErro = 0;

          //(((float)linhasAbertoAcompanhadasComErro / (float)linhasAbertoAcompanhadas) * 100);
          $comErroPerc = $linhasAbertoAcompanhadas==0 ? 0 : round(($linhasAbertoComErro / $linhasAbertoAcompanhadas) * 100, 3);
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
                  and f.clienteid=$usuario->clienteid
                  group by e.id
              )
              UNION ALL
              (
                  select count(distinct f.id) as total, 'C' as tipo, e.descricao
                  from followup f
                  inner join followup_erros as e on f.errocoletaid = e.id
                  where date(dhimportacao) between date('$di') and  date('$df')
                  and f.errocoletastatus='2'
                  and f.clienteid=$usuario->clienteid
                  group by e.id
              )
              UNION ALL
              (
                  select count(distinct f.id) as total, 'D' as tipo, e.descricao
                  from followup f
                  inner join followup_erros as e on f.errodtpromessaid = e.id
                  where date(dhimportacao) between date('$di') and  date('$df')
                  and f.clienteid=$usuario->clienteid
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
                  'indice' => $total==0 ? 0 : round((($r->total/$total)*100),5),
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
                      and followup.clienteid=$usuario->clienteid
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
                      and followup.clienteid=$usuario->clienteid
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
                      and followup.clienteid=$usuario->clienteid
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
                      and followup.clienteid=$usuario->clienteid
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
                      and followup.clienteid=$usuario->clienteid
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
