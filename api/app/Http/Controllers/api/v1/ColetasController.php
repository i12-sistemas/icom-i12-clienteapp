<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Exception;
use Illuminate\Support\Facades\DB;
use Validator;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use PDF;

use App\Http\Controllers\RetApiController;

use App\Enums\ColetasSituacaoType;
use App\Enums\ColetasEncerramentoTipoType;
use App\Models\Cliente;
use App\Models\Coletas;
use App\Models\Motorista;
use App\Models\ColetasItens;
use App\Models\ColetasEventos;
use App\Models\ColetasNota;

use Illuminate\Support\Facades\Mail;
use App\Jobs\SendMailPadraoJob;

use App\Exports\ColetasExport;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ColetasController extends Controller
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
        $produtosperigosos = isset($request->produtosperigosos) ? $request->produtosperigosos : null;
        $cargaurgente = isset($request->cargaurgente) ? $request->cargaurgente : null;
        $veiculoexclusico = isset($request->veiculoexclusico) ? $request->veiculoexclusico : null;
        $semmotorista = isset($request->semmotorista) ? $request->semmotorista : null;

        $pesoi = isset($request->pesoi) ? floatval($request->pesoi) : null;
        $pesof = isset($request->pesof) ? floatval($request->pesof) : null;

        $ctenumero2 = null;
        if ($request->has('ctenumero2')) {
            $ctenumero2 = json_decode($request->ctenumero2);
            if (!is_array($ctenumero2)) $ctenumero2[] = $ctenumero2;
            $ctenumero2 = count($ctenumero2) > 0 ? $ctenumero2 : null;
        }

        $ctenumero = isset($request->ctenumero) ? intval($request->ctenumero) : null;

        $ctenumero2 = null;
        $ctenumero2vazio = false;
        $ctenumero2naovazio = false;
        if ($request->has('ctenumero2')) {
            $list = json_decode($request->ctenumero2);
            $ctenumero2 = [];
            foreach ($list as $value) {
                if ($value === 'vazio') {
                    $ctenumero2vazio = true;
                } else if ($value === 'naovazio') {
                    $ctenumero2naovazio = true;
                } else {
                    $ctenumero2[] = $value;
                }
            }
            if (!(count($ctenumero2) > 0)) $ctenumero2 = null;
        }


        $clienteorigemstr = isset($request->clienteorigemstr) ? $request->clienteorigemstr : null;
        $motoristastr = isset($request->motoristastr) ? $request->motoristastr : null;
        $regiaostr = isset($request->regiaostr) ? $request->regiaostr : null;
        $enderecocoletastr = isset($request->enderecocoletastr) ? $request->enderecocoletastr : null;
        $clientedestinostr = isset($request->clientedestinostr) ? $request->clientedestinostr : null;
        $cidadedestinostr = isset($request->cidadedestinostr) ? $request->cidadedestinostr : null;

        $find = isset($request->find) ? utf8_decode($request->find) : null;
        $situacao = null;
        if (isset($request->situacao)) {
            $situacao = explode(",", $request->situacao);
            if (!is_array($situacao)) $situacao[] = $situacao;
            $situacao = count($situacao) > 0 ? $situacao : null;
        }
        $origem = null;
        if (isset($request->origem)) {
            $origem = explode(",", $request->origem);
            if (!is_array($origem)) $origem[] = $origem;
            $origem = count($origem) > 0 ? $origem : null;
        }
        $motoristas = null;
        if (isset($request->motoristas)) {
            $motoristas = explode(",", $request->motoristas);
            if (!is_array($motoristas)) $motoristas[] = $motoristas;
            $motoristas = count($motoristas) > 0 ? $motoristas : null;
        }

        $clienteorigem = null;
        if (isset($request->clienteorigem)) {
            $clienteorigem = explode(",", $request->clienteorigem);
            if (!is_array($clienteorigem)) $clienteorigem[] = $clienteorigem;
            $clienteorigem = count($clienteorigem) > 0 ? $clienteorigem : null;
        }

        $clientedestino = null;
        if (isset($request->clientedestino)) {
            $clientedestino = explode(",", $request->clientedestino);
            if (!is_array($clientedestino)) $clientedestino[] = $clientedestino;
            $clientedestino = count($clientedestino) > 0 ? $clientedestino : null;
        }

        $regiao = null;
        if (isset($request->regiao)) {
            $regiao = explode(",", $request->regiao);
            if (!is_array($regiao)) $regiao[] = $regiao;
            $regiao = count($regiao) > 0 ? $regiao : null;
        }

        $cidadedestino = null;
        if (isset($request->cidadedestino)) {
            $cidadedestino = explode(",", $request->cidadedestino);
            if (!is_array($cidadedestino)) $cidadedestino[] = $cidadedestino;
            $cidadedestino = count($cidadedestino) > 0 ? $cidadedestino : null;
        }
        $cidades = null;
        if (isset($request->cidades)) {
            $cidades = explode(",", $request->cidades);
            if (!is_array($cidades)) $cidades[] = $cidades;
            $cidades = count($cidades) > 0 ? $cidades : null;
        }

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

        // se existir numero, cancela outros filtros
        $numero = isset($request->numero) ? intVal($request->numero) : null;
        if ($numero) {
            if (!($numero>0)) $numero = null;

            if ($numero>0) {
                $dhcoletaf = null;
                $dhcoletaf = null;
                $dhbaixai = null;
                $dhbaixaf = null;
                $situacao = null;
                $origem = null;
                $find  = null;
            }
        } else {
            if ($find != '') {
                $n = intval($find);
                if ($n > 0) $numero = $n;
            }
        }

        $perpage = isset($request->perpage) ? $request->perpage : 25;
        $query = Coletas::select(DB::raw('coletas.*'))
                    ->leftJoin('cliente as clienteorigem', 'coletas.origemclienteid', '=', 'clienteorigem.id')
                    ->leftJoin('cliente as clientedestino', 'coletas.destinoclienteid', '=', 'clientedestino.id')
                        ->leftJoin('cidades as cidadedestino', 'clientedestino.cidadeid', '=', 'cidadedestino.id')
                    ->leftJoin('cidades as cidadecoleta', 'coletas.endcoleta_cidadeid', '=', 'cidadecoleta.id')
                    ->leftJoin('motorista', 'coletas.motoristaid', '=', 'motorista.id')
                    ->with( 'motorista', 'created_usuario', 'updated_usuario', 'clienteorigem', 'clientedestino', 'coletacidade', 'coletaregiao', 'orcamento', 'itens' )
                    ->when($find, function ($query) use ($find) {
                      return $query->where(function($query2) use ($find) {
                        return $query2->where('coletas.chavenota', 'like', '%'.$find.'%')
                                ->orWhere('coletas.gestaocliente_itenscomprador', 'like', '%'.$find.'%')
                                ->orWhere('coletas.gestaocliente_comprador', 'like','%'. $find.'%')
                                ->orWhere('coletas.contatonome', 'like','%'. $find.'%')
                                ->orWhere('coletas.contatoemail', 'like', '%'.$find.'%')
                                ->orWhere('coletas.obs', 'like','%'. $find.'%')
                                ->orWhere('coletas.endcoleta_cep', 'like', '%'.$find.'%')

                                ->orWhere('cidadecoleta.cidade', 'like', '%'.$find.'%')
                                ->orWhere('cidadecoleta.estado', 'like', '%'.$find.'%')
                                ->orWhere('cidadecoleta.uf', 'like','%'. $find.'%')

                                ->orWhere('motorista.nome', 'like', '%'.$find.'%')
                                ->orWhere('motorista.apelido', 'like','%'. $find.'%')

                                ->orWhere('clienteorigem.razaosocial', 'like', '%'.$find.'%')
                                ->orWhere('clienteorigem.fantasia', 'like', '%'.$find.'%')
                                ->orWhere('clienteorigem.cnpj', 'like','%'. $find.'%')
                                ->orWhere('clientedestino.razaosocial', 'like', '%'.$find.'%')
                                ->orWhere('clientedestino.fantasia', 'like', '%'.$find.'%')
                                ->orWhere('clientedestino.cnpj', 'like', '%'.$find.'%')
                                ;
                      });
                    })
                    ->when(isset($request->clienteorigemstr) && ($clienteorigemstr ? $clienteorigemstr !== '' : false), function ($query) use ($clienteorigemstr)  {
                        return $query->where(function($query2) use ($clienteorigemstr) {
                            return $query2->where('clienteorigem.razaosocial', 'like', '%'.$clienteorigemstr.'%')
                                ->orWhere('clienteorigem.fantasia', 'like', '%'.$clienteorigemstr.'%');
                        });
                    })
                    ->when(isset($request->clientedestinostr) && ($clientedestinostr ? $clientedestinostr !== '' : false), function ($query) use ($clientedestinostr)  {
                        return $query->where(function($query2) use ($clientedestinostr) {
                            return $query2->where('clientedestino.razaosocial', 'like', '%'.$clientedestinostr.'%')
                            ->orWhere('clientedestino.fantasia', 'like', '%'.$clientedestinostr.'%');
                        });
                    })
                    ->when(isset($request->motoristastr) && ($motoristastr ? $motoristastr !== '' : false), function ($query) use ($motoristastr)  {
                        return $query->where(function($query2) use ($motoristastr) {
                            return $query2->where('motorista.nome', 'like', '%'.$motoristastr.'%')
                            ->orWhere('motorista.apelido', 'like', '%'.$motoristastr.'%');
                        });
                    })

                    ->when(isset($request->enderecocoletastr) && ($enderecocoletastr ? $enderecocoletastr !== '' : false), function ($query) use ($enderecocoletastr)  {
                        return $query->where(function($query2) use ($enderecocoletastr) {
                            return $query2->where('cidadecoleta.cidade', 'like', '%'.$enderecocoletastr.'%')
                            ->orWhere('cidadecoleta.uf', 'like', '%'.$enderecocoletastr.'%');
                        });
                    })

                    ->when(isset($request->cidadedestinostr) && ($cidadedestinostr ? $cidadedestinostr !== '' : false), function ($query) use ($cidadedestinostr)  {
                        return $query->where(function($query2) use ($cidadedestinostr) {
                            return $query2->where('cidadedestino.cidade', 'like', '%'.$cidadedestinostr.'%')
                            ->orWhere('cidadedestino.uf', 'like', '%'.$cidadedestinostr.'%');
                        });
                    })

                    ->when(isset($request->ctenumero) && ($ctenumero > 0) , function ($query) use ($ctenumero)  {
                        return $query->where('coletas.ctenumero', '=', $ctenumero);
                    })
                    ->when(isset($request->ctenumero2) && ($ctenumero2 ? count($ctenumero2) > 0 : false) , function ($query) use ($ctenumero2)  {
                        return $query->whereIn('coletas.ctenumero', $ctenumero2);
                    })
                    ->when(isset($request->ctenumero2) && ($ctenumero2vazio), function ($query) {
                        return $query->whereRaw('ifnull(coletas.ctenumero,"") = ""');
                    })
                    ->when(isset($request->ctenumero2) && ($ctenumero2naovazio), function ($query) {
                        return $query->whereRaw('ifnull(coletas.ctenumero,"") <> ""');
                    })


                    ->when(isset($request->regiaostr) && ($regiaostr ? $regiaostr !== '' : false), function ($query) use ($regiaostr)  {
                        return $query->Where('cidadecoleta.regiaoid', intval($regiaostr));
                    })
                    ->when($numero, function ($query, $numero) {
                        return $query->Where('coletas.id', $numero);
                    })
                    ->when(isset($request->situacao) && ($situacao != null), function ($query, $t) use ($situacao) {
                        return $query->WhereIn('coletas.situacao', $situacao);
                    })
                    ->when(isset($request->origem) && ($origem != null), function ($query, $t) use ($origem) {
                        return $query->WhereIn('coletas.origem', $origem);
                    })
                    ->when(isset($request->motoristas) && ($motoristas != null), function ($query, $t) use ($motoristas) {
                        return $query->WhereIn('coletas.motoristaid', $motoristas);
                    })
                    ->when(isset($request->regiao) && ($regiao != null), function ($query, $t) use ($regiao) {
                        return $query->WhereIn('cidadecoleta.regiaoid', $regiao);
                    })
                    ->when(isset($request->cidades) && ($cidades != null), function ($query, $t) use ($cidades) {
                        return $query->WhereIn('coletas.endcoleta_cidadeid', $cidades);
                    })
                    ->when(isset($request->cidadedestino) && ($cidadedestino != null), function ($query, $t) use ($cidadedestino) {
                        return $query->WhereIn('clientedestino.cidadeid', $cidadedestino);
                    })
                    ->when(isset($request->clientedestino) && ($clientedestino != null), function ($query, $t) use ($clientedestino) {
                        return $query->WhereIn('clientedestino.id', $clientedestino);
                    })
                    ->when(isset($request->clienteorigem) && ($clienteorigem != null), function ($query, $t) use ($clienteorigem) {
                        return $query->WhereIn('coletas.origemclienteid', $clienteorigem);
                    })
                    ->when(isset($request->produtosperigosos), function ($query, $t) use ($produtosperigosos) {
                        return $query->Where('coletas.produtosperigosos', '=', toBool($produtosperigosos) ? 1 : 0);
                    })
                    ->when(isset($request->cargaurgente), function ($query, $t) use ($cargaurgente) {
                        return $query->Where('coletas.cargaurgente', '=', toBool($cargaurgente) ? 1 : 0);
                    })
                    ->when(isset($request->veiculoexclusico), function ($query, $t) use ($veiculoexclusico) {
                        return $query->Where('coletas.veiculoexclusico', '=', toBool($veiculoexclusico) ? 1 : 0);
                    })
                    ->when(isset($request->pesoi), function ($query) use ($pesoi) {
                        return $query->Where('coletas.peso', '>=', $pesoi);
                    })
                    ->when(isset($request->pesof), function ($query) use ($pesof) {
                        return $query->Where('coletas.peso', '<=', $pesof);
                    })
                    ->when(isset($request->dhcoletai), function ($query) use ($dhcoletai) {
                        return $query->Where(DB::Raw('date(coletas.dhcoleta)'), '>=', $dhcoletai);
                    })
                    ->when(isset($request->dhcoletaf), function ($query) use ($dhcoletaf) {
                        return $query->Where(DB::Raw('date(coletas.dhcoleta)'), '<=', $dhcoletaf);
                    })
                    ->when(isset($request->dhbaixai), function ($query) use ($dhbaixai) {
                        return $query->Where(DB::Raw('date(coletas.dhbaixa)'), '>=', $dhbaixai);
                    })
                    ->when(isset($request->dhbaixaf), function ($query) use ($dhbaixaf) {
                        return $query->Where(DB::Raw('date(coletas.dhbaixa)'), '<=', $dhbaixaf);
                    })
                    ->when(isset($request->semmotorista), function ($query) use ($semmotorista) {
                        return $query->whereRaw('if(?=1, coletas.motoristaid is null, false)', [$semmotorista]);
                    })
                    ->when($request->orderby, function ($query) use ($orderby) {
                        foreach ($orderby as $key => $value) {
                            $query->orderByRaw($key  . ' ' . $value);
                        }
                        return $query;
                    });

        $coletas = $query->paginate($perpage);
        $totalpeso = $query->sum('peso');
        $counters = [
            'peso' => $totalpeso
        ];

        $dados = [];
        foreach ($coletas as $coleta) {
            $dados[] = $coleta->export();
        }
        $ret->counters = $counters;
        $ret->data = $dados;
        $ret->sortby = $sortby;
        $ret->descending = ($descending == 'desc' ? 'desc' : 'asc');
        $ret->collection = $coletas;
        $ret->ok = true;
      } catch (\Throwable $th) {
        $ret->msg = $th->getMessage();
      }
      return $ret->toJson();
    }


    public function ultimas_coletas_porregiao(Request $request, $regiaoid)
    {
      $ret = new RetApiController;
      try {

        $sql = "select count(distinct dados.id) as qtde, dados.motoristaid, max(date(dhcoleta)) as ult_dhcoleta, max(date(dhbaixa)) as ult_dhbaixa
        from (
            select coletas.id, coletas.dhcoleta, coletas.motoristaid, coletas.dhbaixa
            from coletas
            inner join cidades on cidades.id=coletas.endcoleta_cidadeid
            where coletas.situacao='2' and cidades.regiaoid=? and date(coletas.dhcoleta)>=date_add(now(), interval -1 month)
            order by coletas.dhbaixa desc, coletas.dhcoleta desc
        ) dados
        group by dados.motoristaid
        order by count(distinct dados.id) desc
        limit 0,10";

        $dataset = \DB::select( DB::raw($sql), [$regiaoid]);
        $motoristasIDs = [];
        foreach ($dataset as $key => $row) {
            $motoristasIDs[] = $row->motoristaid;
        }

        $motoristas = Motorista::whereIn('id', $motoristasIDs)->get();

        $dadosregiao = [];
        foreach ($dataset as $key => $row) {
            $motorista = $motoristas->find($row->motoristaid);
            if ($motorista) {
                $dadosregiao[] = [
                    'coletasqtde' => $row->qtde,
                    'coletasultdhcoleta' => $row->ult_dhcoleta,
                    'coletasultdhbaixa' => $row->ult_dhbaixa,
                    'motorista' => $motorista->exportsmall()
                ];

            }

        }

        // por fornecedor
        $coleta = null;
        $fornecedorid = isset($request->fornecedorid) ? intval($request->fornecedorid) : null;
        if ($fornecedorid > 0) {
            $coleta = Coletas::where('coletas.situacao', '=', '2')
                        ->where('coletas.origemclienteid', '=', $fornecedorid)
                        ->orderBy('coletas.dhbaixa', 'desc')
                        ->orderBy('coletas.dhcoleta', 'desc')
                        ->first();
        }

        $ret->data = [
            'ultima_coleta' => $coleta ? $coleta->export(true) : null,
            'resumo_regiao' => $dadosregiao
        ];
        $ret->ok = true;
      } catch (\Throwable $th) {
        $ret->msg = $th->getMessage();
      }
      return $ret->toJson();
    }

    public function dashboard1(Request $request)
    {
      $ret = new RetApiController;
      try {

        $emaberto = Coletas::whereIn('situacao', ['0','1'])->count();
        $cargaurgente = Coletas::whereIn('situacao', ['0','1'])->where('cargaurgente', 1)->count();
        $semmotorista = Coletas::whereIn('situacao', ['0','1'])->whereNull('motoristaid')->count();
        $revisaoorcamento = Coletas::where('situacao', '=', '0')->where('origem', '=', '2')->count();
        $naoliberado = Coletas::where('situacao', '0')->count();

        $today = Carbon::today();
        $yesterday = Carbon::yesterday();
        $futuro = Coletas::whereIn('situacao', ['0','1'])->where(DB::Raw('date(dhcoleta)'), '>', $today->toDateString())->count();
        $hoje = Coletas::whereIn('situacao', ['0','1'])->where(DB::Raw('date(dhcoleta)'), '=', $today->toDateString())->count();
        $ontem = Coletas::whereIn('situacao', ['0','1'])->where(DB::Raw('date(dhcoleta)'), '=', $yesterday->toDateString())->count();
        $atrasado = Coletas::whereIn('situacao', ['0','1'])->where(DB::Raw('date(dhcoleta)'), '<', $yesterday->toDateString())->count();

        // encerradas!
        $encerrado_hoje = Coletas::where('situacao', '=', '2')->where(DB::Raw('date(dhbaixa)'), Carbon::today()->toDateString())->count();
        $encerrado_estasemana = Coletas::where('situacao', '=', '2')->whereBetween(DB::Raw('date(dhbaixa)'), [Carbon::today()->startOfWeek()->toDateString(), Carbon::today()->toDateString()])->count();
        $encerrado_estames = Coletas::where('situacao', '=', '2')->whereBetween(DB::Raw('date(dhbaixa)'), [Carbon::today()->startOfMonth()->toDateString(), Carbon::today()->toDateString()])->count();

        // cancelados!
        $cancelados_hoje = Coletas::where('situacao', '=', '3')->where(DB::Raw('date(dhbaixa)'), Carbon::today()->toDateString())->count();
        $cancelados_estasemana = Coletas::where('situacao', '=', '3')->whereBetween(DB::Raw('date(dhbaixa)'), [Carbon::today()->startOfWeek()->toDateString(), Carbon::today()->toDateString()])->count();
        $cancelados_estames = Coletas::where('situacao', '=', '3')->whereBetween(DB::Raw('date(dhbaixa)'), [Carbon::today()->startOfMonth()->toDateString(), Carbon::today()->toDateString()])->count();


        $dados = [
            'emaberto' => [
                'total' => $emaberto,
                'cargaurgente' => $cargaurgente,
                'semmotorista' => $semmotorista,
                'revisaoorcamento' => $revisaoorcamento,
                'naoliberado' => $naoliberado,
                'futuro' => $futuro,
                'hoje' => $hoje,
                'ontem' => $ontem,
                'atrasado' => $atrasado
            ],
            'encerrados' => [
                'hoje' => $encerrado_hoje,
                'semana' => $encerrado_estasemana,
                'mes' => $encerrado_estames
            ],
            'cancelados' => [
                'hoje' => $cancelados_hoje,
                'semana' => $cancelados_estasemana,
                'mes' => $cancelados_estames
            ]
        ];
        $ret->data = $dados;
        $ret->ok = true;
      } catch (\Throwable $th) {
        $ret->msg = $th->getMessage();
      }
      return $ret->toJson();
    }

    public function eventos_list(Request $request, $idcoleta)
    {
      $ret = new RetApiController;
      try {

        if (!($idcoleta > 0)) throw new Exception("Coleta não foi encontrada");
        $coleta = Coletas::find($idcoleta);
        if (!$coleta) throw new Exception("Coleta não foi encontrada");

        $sortby = isset($request->sortby) ? $request->sortby : 'created_at';
        $descending = isset($request->descending) ? $request->descending : 'desc';
        // $dhcoletai = isset($request->dhcoletai) ? $request->dhcoletai : null;
        // $dhcoletaf = isset($request->dhcoletaf) ? $request->dhcoletaf : null;
        // $find = isset($request->find) ? utf8_decode($request->find) : null;
        // $situacao = isset($request->situacao) ? $request->situacao : null;
        // if ($situacao) {
        //     $situacao = explode(",", $situacao);
        //     $nSituacao = [];
        //     foreach ($situacao as $value) {
        //         $n = intval($value);
        //         if ($n >= 0 ) $nSituacao[] = $n;
        //     }
        //     $situacao = count($nSituacao) > 0 ? $nSituacao : null;
        // }

        $perpage = isset($request->perpage) ? $request->perpage : 25;
        $eventos = $coleta->eventos()->with('created_usuario')
                    // ->when($find, function ($query, $find) {
                    //   return $query->Where('coletas.chavenota', 'like', $find.'%')
                    //         ->orWhere('coletas.gestaocliente_itenscomprador', 'like', $find.'%')
                    //         ->orWhere('coletas.gestaocliente_comprador', 'like', $find.'%')
                    //         // ->orWhere('coletas.especie', 'like', $find.'%')
                    //         // ->orWhere('coletas.endcoleta_logradouro', 'like', $find.'%')
                    //         // ->orWhere('coletas.endcoleta_endereco', 'like', $find.'%')
                    //         // ->orWhere('coletas.endcoleta_numero', 'like', $find.'%')
                    //         // ->orWhere('coletas.endcoleta_complemento', 'like', $find.'%')
                    //         ->orWhere('coletas.contatonome', 'like', $find.'%')
                    //         ->orWhere('coletas.contatoemail', 'like', $find.'%')
                    //         ->orWhere('coletas.obs', 'like', $find.'%')
                    //         // ->orWhere('coletas.endcoleta_bairro', 'like', $find.'%')
                    //         ->orWhere('coletas.endcoleta_cep', 'like', $find.'%')

                    //         ->orWhere('cidadecoleta.cidade', 'like', $find.'%')
                    //         ->orWhere('cidadecoleta.estado', 'like', $find.'%')
                    //         ->orWhere('cidadecoleta.uf', 'like', $find.'%')

                    //         ->orWhere('motorista.nome', 'like', $find.'%')
                    //         ->orWhere('motorista.apelido', 'like', $find.'%')
                    //         // ->orWhere('motorista.username', 'like', $find.'%')

                    //         ->orWhere('clienteorigem.razaosocial', 'like', $find.'%')
                    //         ->orWhere('clienteorigem.fantasia', 'like', $find.'%')
                    //         ->orWhere('clienteorigem.cnpj', 'like', $find.'%')
                    //         ->orWhere('clientedestino.razaosocial', 'like', $find.'%')
                    //         ->orWhere('clientedestino.fantasia', 'like', $find.'%')
                    //         ->orWhere('clientedestino.cnpj', 'like', $find.'%')
                    //         ;
                    // })
                    // ->when($numero, function ($query, $numero) {
                    //     return $query->Where('coletas.id', $numero);
                    // })
                    // ->when($situacao, function ($query, $situacao) {
                    //     return $query->WhereIn('coletas.situacao', $situacao);
                    // })
                    // ->when($dhcoletai, function ($query, $dhcoletai) {
                    //     return $query->Where(DB::Raw('date(coletas.dhcoleta)'), '>=', $dhcoletai);
                    // })
                    // ->when($dhcoletaf, function ($query, $dhcoletaf) {
                    //     return $query->Where(DB::Raw('date(coletas.dhcoleta)'), '<=', $dhcoletaf);
                    // })
                    ->orderBy($sortby, ($descending == 'desc' ? 'desc' : 'asc'))
                    ->orderby('coletas_eventos.id', 'desc')
                    ->paginate($perpage);
        $dados = [];
        foreach ($eventos as $evento) {
            $dados[] = $evento->toObject(true);
        }
        $ret->data = $dados;
        $ret->sortby = $sortby;
        $ret->descending = ($descending == 'desc' ? 'desc' : 'asc');
        $ret->collection = $eventos;
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

        $coleta = Coletas::find($find);
        if (!$coleta) throw new Exception("Coleta não foi encontrada");

        $ret->data = $coleta->toObject(False);
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
            'situacao' => ['required'],
            'dhcoleta' => ['required', 'date'],
            'peso' => ['required', 'min:0'],
            'qtde' => ['required', 'min:0'],
            'especie' => ['max:150'],
            'veiculoexclusico' => ['required', 'boolean'],
            'cargaurgente' => ['required', 'boolean'],
            'produtosperigosos' => ['required', 'boolean'],
            'clienteorigemid' => ['required', 'exists:cliente,id'],
            'clientedestinoid' => ['required', 'exists:cliente,id'],
            // 'contatoemail' => ['email:rfc,dns'],
            // 'motoristaid' => ['exists:motorista,id'],
            'endcoleta_cidadeid' => ['required', 'exists:cidades,id']
            // 'cnpj' => [
            //   'string',
            //   Rule::unique('cliente')->ignore(isset($request->id) ? intVal($request->id) : 0),
            // ]
        ];
        $messages = [
            'size' => 'O campo :attribute, deverá ter :max caracteres.',
            'integer' => 'O conteudo do campo :attribute deverá ser um número inteiro.',
            'unique' => 'O conteudo do campo :attribute já foi cadastrado.',
            'required' => 'O conteudo do campo :attribute é obrigatório.',
            'email' => 'O conteudo do campo :attribute deve ser um e-mail valido.',
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

        $id = isset($request->id) ? intVal($request->id) : 0;
        $action =  $id>0 ? 'update' : 'add';

        if ($action=='update') {
            $coleta = Coletas::find($id);
            if (!$coleta) throw new Exception("Coleta não foi encontrada");

            if (!in_array($coleta->situacao, [ColetasSituacaoType::tcsBloqueado, ColetasSituacaoType::tcsLiberado])) {
                throw new Exception("Situação atual da coleta não permite alteração - Situação: " . $coleta->situacao . " - " . ColetasSituacaoType::getDescription($coleta->situacao));
            }
        } else {
            //adding
            $origem = isset($request->origem) ? intVal($request->origem) : null;
            if (!$origem) throw new Exception("Campo origem não foi informado!");
        }

        $chavenota = isset($request->chavenota) ? $request->chavenota : '';
        if ($chavenota !== '') {
            if (!testaChaveNFe($chavenota)) throw new Exception("Chave da nota inválida!");

            $nfecheck = Coletas::where('chavenota', '=', $chavenota)->whereRaw('if(?>0, not(id=?), true)', [$id, $id])->first();
            if ($nfecheck) throw new Exception("A chave informada já foi lançada na coleta # " . $nfecheck->id . ' do dia ' . $nfecheck->dhcoleta->format('d/m/Y') );

            $chavedecode = decodeChaveNFe($chavenota);

            $cliente = Cliente::find($request->clienteorigemid);
            if ($cliente->cnpj !== $chavedecode["CNPJ"])
                throw new Exception("O CNPJ emissor da nota " . formatCnpjCpf($chavedecode["CNPJ"]) . " da chave é diferente do CNPJ do remetente " . formatCnpjCpf($cliente->cnpj) . "");
        }


      } catch (\Throwable $th) {
        $ret->msg = $th->getMessage();
        return $ret->toJson();
      }

      try {
        DB::beginTransaction();



        if ($action=='add') {
            $coleta = new Coletas();
            $coleta->created_usuarioid = $usuario->id;
            $coleta->origem = $request->origem;
        }
        $coleta->origemclienteid = $request->clienteorigemid;
        $coleta->destinoclienteid = $request->clientedestinoid;
        if (!isset($request->motoristaid)) {
            $coleta->motoristaid = null;
        } else {
            $coleta->motoristaid = $request->motoristaid;
        }

        $coleta->chavenota = $chavenota;

        $coleta->dhcoleta = $request->dhcoleta;

        $coleta->contatonome = $request->contatonome;
        $coleta->contatoemail = $request->contatoemail;
        $coleta->peso = $request->peso;
        $coleta->especie = $request->especie;
        $coleta->qtde = $request->qtde;
        $coleta->obs = $request->obs;

        // $coleta->liberado = $request->liberado ? 1 : 0;
        $coleta->veiculoexclusico = $request->veiculoexclusico ? 1 : 0;
        $coleta->cargaurgente = $request->cargaurgente ? 1 : 0;
        $coleta->produtosperigosos = $request->produtosperigosos ? 1 : 0;

        $coleta->gestaoclienteOrdemcompra = $request->gestaocliente_ordemcompra;
        $coleta->gestaoclienteComprador = $request->gestaocliente_comprador;
        $coleta->gestaoclienteItenscomprador = $request->gestaocliente_itenscomprador;

        $coleta->situacao = $request->situacao;

        $coleta->endcoleta_logradouro = $request->endcoleta_logradouro;
        $coleta->endcoleta_endereco = $request->endcoleta_endereco;
        $coleta->endcoleta_numero = $request->endcoleta_numero;
        $coleta->endcoleta_bairro = $request->endcoleta_bairro;
        $coleta->endcoleta_cep = $request->endcoleta_cep;
        $coleta->endcoleta_complemento = $request->endcoleta_complemento;
        $coleta->endcoleta_cidadeid = $request->endcoleta_cidadeid;
        // if (isset($request->fone1)) $coleta->fone1 = $request->fone1;
        // if (isset($request->fone2)) $coleta->fone2 = $request->fone2;
        // if (isset($request->obs)) $coleta->obs = $request->obs;
        // if (isset($request->fantasia_followup)) $coleta->fantasia_followup = $request->fantasia_followup;
        $coleta->updated_usuarioid = $usuario->id;

        $coleta->save();

        if (isset($request->itens)) {
            $actions = $request->itens;
            foreach ($actions as $elemento) {
                $elemento  =(object)$elemento;
                $elemento->item = (object)$elemento->item;
                if ($elemento->action == 'delete') {
                    $del = ColetasItens::find($elemento->item->id)->delete();
                    if (!$del) throw new Exception("Ietm não foi excluído - " . $elemento->item->produtodescricao);
                }
                if ($elemento->action == 'update') {
                    $item = ColetasItens::find($elemento->item->id);
                    if ($item) {
                        if ((isset($elemento->item->produtoid) ? $elemento->item->produtoid : 0) > 0) {
                            $item->produtoid = $elemento->item->produtoid;
                            $item->produtodescricao = $item->produto->nome;
                        } else {
                            $item->produtoid = null;
                            $item->produtodescricao = $elemento->item->produtonome;
                        }
                        $item->qtde = $elemento->item->qtde;
                        $item->embalagem = $elemento->item->embalagem;
                        $item->updated_usuarioid = $usuario->id;
                        $ins = $item->save();
                        if (!$ins) throw new Exception("Item não foi atualizado - " . $item->produtodescricao);
                    }
                }
                if ($elemento->action == 'insert') {
                    $item = new ColetasItens();
                    if ((isset($elemento->item->produtoid) ? $elemento->item->produtoid : 0) > 0) {
                        $item->produtoid = $elemento->item->produtoid;
                        $item->produtodescricao = $item->produto->nome;
                    } else {
                        $item->produtoid = null;
                        $item->produtodescricao = $elemento->item->produtonome;
                    }
                    $item->qtde = $elemento->item->qtde;
                    $item->embalagem = $elemento->item->embalagem;
                    $item->created_usuarioid = $usuario->id;
                    $item->updated_usuarioid = $usuario->id;
                    $item->coletaid = $coleta->id;
                    $ins = $item->save();
                    if (!$ins) throw new Exception("Item não foi inserido - " . $item->produtodescricao);
                }
            }

        }


        // if ($action=='add') {
        //     $logevento = new ColetasEventos();
        //     $logevento->created_at = Carbon::now();
        //     $logevento->created_usuarioid = $usuario->id;
        //     $logevento->coletaid = $coleta->id;
        //     $logevento->ip = getIp();
        //     $logevento->detalhe  = 'Nova coleta inserida';
        //     $logevento->tipo  = 'insert';
        //     $ins = $logevento->save();
        //     if (!$ins) throw new Exception("Log de evento não foi inserido");
        // } else {
        //     $logevento = new ColetasEventos();
        //     $logevento->created_at = Carbon::now();
        //     $logevento->created_usuarioid = $usuario->id;
        //     $logevento->coletaid = $coleta->id;
        //     $logevento->ip = getIp();
        //     $logevento->detalhe  = 'Coleta atualizada' . '\n' . json_encode($depara);
        //     $logevento->tipo  = 'update';
        //     $ins = $logevento->save();
        //     if (!$ins) throw new Exception("Log de evento não foi inserido");
        // }


        DB::commit();

        $ret->id = $coleta->id;
        $ret->data = $coleta->toObject(false);
        $ret->msg = $action;
        $ret->ok = true;


      } catch (\Throwable $th) {
        DB::rollBack();
        $ret->msg = $th->getMessage();
      }


      return $ret->toJson();
    }

    public function addColetaAvulsa($coletanotaid)
    {
      $ret = new RetApiController;
      try {

        $usuario = session('usuario');
        if (!$usuario) throw new Exception('Nenhum usuário autenticado');

        $nota = ColetasNota::find($coletanotaid);
        if (!$nota) throw new Exception("Nota avulsa não foi encontrada");
        if ($nota->coletaavulsa !== 1) throw new Exception("Nota não é de uma coleta avulsa");
        if ($nota->coletaavulsaincluida !== 0) throw new Exception("Nota já foi incluida");
        if ($nota->idcoleta > 0) throw new Exception("Nota já foi incluida pela coleta " . $nota->idcoleta);


      } catch (\Throwable $th) {
        $ret->msg = $th->getMessage();
        return $ret->toJson();
      }

      try {
        DB::beginTransaction();

        $coleta = new Coletas();
        $coleta->created_usuarioid = 0; //usuario de sistema
        $coleta->origem = 4; //1=interno direto, 2=interno orcamento, 3=painel do cliente, 4=Coleta avulsa aplicativo


        $coleta->origemclienteid = $request->clienteorigemid;
        $coleta->destinoclienteid = $request->clientedestinoid;
        if (!isset($request->motoristaid)) {
            $coleta->motoristaid = null;
        } else {
            $coleta->motoristaid = $request->motoristaid;
        }

        $coleta->dhcoleta = $request->dhcoleta;

        $coleta->contatonome = $request->contatonome;
        $coleta->contatoemail = $request->contatoemail;
        $coleta->peso = $request->peso;
        $coleta->especie = $request->especie;
        $coleta->qtde = $request->qtde;
        $coleta->obs = $request->obs;

        // $coleta->liberado = $request->liberado ? 1 : 0;
        $coleta->veiculoexclusico = $request->veiculoexclusico ? 1 : 0;
        $coleta->cargaurgente = $request->cargaurgente ? 1 : 0;
        $coleta->produtosperigosos = $request->produtosperigosos ? 1 : 0;

        $coleta->gestaoclienteOrdemcompra = $request->gestaocliente_ordemcompra;
        $coleta->gestaoclienteComprador = $request->gestaocliente_comprador;
        $coleta->gestaoclienteItenscomprador = $request->gestaocliente_itenscomprador;

        $coleta->situacao = $request->situacao;

        $coleta->endcoleta_logradouro = $request->endcoleta_logradouro;
        $coleta->endcoleta_endereco = $request->endcoleta_endereco;
        $coleta->endcoleta_numero = $request->endcoleta_numero;
        $coleta->endcoleta_bairro = $request->endcoleta_bairro;
        $coleta->endcoleta_cep = $request->endcoleta_cep;
        $coleta->endcoleta_complemento = $request->endcoleta_complemento;
        $coleta->endcoleta_cidadeid = $request->endcoleta_cidadeid;
        // if (isset($request->fone1)) $coleta->fone1 = $request->fone1;
        // if (isset($request->fone2)) $coleta->fone2 = $request->fone2;
        // if (isset($request->obs)) $coleta->obs = $request->obs;
        // if (isset($request->fantasia_followup)) $coleta->fantasia_followup = $request->fantasia_followup;
        $coleta->updated_usuarioid = $usuario->id;

        $coleta->save();

        if (isset($request->itens)) {
            $actions = $request->itens;
            foreach ($actions as $elemento) {
                $elemento  =(object)$elemento;
                $elemento->item = (object)$elemento->item;
                if ($elemento->action == 'delete') {
                    $del = ColetasItens::find($elemento->item->id)->delete();
                    if (!$del) throw new Exception("Ietm não foi excluído - " . $elemento->item->produtodescricao);
                }
                if ($elemento->action == 'update') {
                    $item = ColetasItens::find($elemento->item->id);
                    if ($item) {
                        if ((isset($elemento->item->produtoid) ? $elemento->item->produtoid : 0) > 0) {
                            $item->produtoid = $elemento->item->produtoid;
                            $item->produtodescricao = $item->produto->nome;
                        } else {
                            $item->produtoid = null;
                            $item->produtodescricao = $elemento->item->produtonome;
                        }
                        $item->qtde = $elemento->item->qtde;
                        $item->embalagem = $elemento->item->embalagem;
                        $item->updated_usuarioid = $usuario->id;
                        $ins = $item->save();
                        if (!$ins) throw new Exception("Item não foi atualizado - " . $item->produtodescricao);
                    }
                }
                if ($elemento->action == 'insert') {
                    $item = new ColetasItens();
                    if ((isset($elemento->item->produtoid) ? $elemento->item->produtoid : 0) > 0) {
                        $item->produtoid = $elemento->item->produtoid;
                        $item->produtodescricao = $item->produto->nome;
                    } else {
                        $item->produtoid = null;
                        $item->produtodescricao = $elemento->item->produtonome;
                    }
                    $item->qtde = $elemento->item->qtde;
                    $item->embalagem = $elemento->item->embalagem;
                    $item->created_usuarioid = $usuario->id;
                    $item->updated_usuarioid = $usuario->id;
                    $item->coletaid = $coleta->id;
                    $ins = $item->save();
                    if (!$ins) throw new Exception("Item não foi inserido - " . $item->produtodescricao);
                }
            }

        }


        // if ($action=='add') {
        //     $logevento = new ColetasEventos();
        //     $logevento->created_at = Carbon::now();
        //     $logevento->created_usuarioid = $usuario->id;
        //     $logevento->coletaid = $coleta->id;
        //     $logevento->ip = getIp();
        //     $logevento->detalhe  = 'Nova coleta inserida';
        //     $logevento->tipo  = 'insert';
        //     $ins = $logevento->save();
        //     if (!$ins) throw new Exception("Log de evento não foi inserido");
        // } else {
        //     $logevento = new ColetasEventos();
        //     $logevento->created_at = Carbon::now();
        //     $logevento->created_usuarioid = $usuario->id;
        //     $logevento->coletaid = $coleta->id;
        //     $logevento->ip = getIp();
        //     $logevento->detalhe  = 'Coleta atualizada' . '\n' . json_encode($depara);
        //     $logevento->tipo  = 'update';
        //     $ins = $logevento->save();
        //     if (!$ins) throw new Exception("Log de evento não foi inserido");
        // }


        DB::commit();

        $ret->id = $coleta->id;
        $ret->data = $coleta->toObject(false);
        $ret->msg = $action;
        $ret->ok = true;


      } catch (\Throwable $th) {
        DB::rollBack();
        $ret->msg = $th->getMessage();
      }


      return $ret->toJson();
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
            $coletasrecebidas = [];
            foreach ($data as $coleta) {
                $ids[] = $coleta['id'];
                $coletasrecebidas[$coleta['id']] = $coleta;
            }

            $coletas = Coletas::whereIn('id', $ids)->get();
            if (!$coletas) throw new Exception("Coleta não foi encontrada");

            foreach ($coletas as $coleta) {
                if (!in_array($coleta->situacao, [ColetasSituacaoType::tcsBloqueado, ColetasSituacaoType::tcsLiberado])) {
                    throw new Exception("Situação atual da coleta " . $coleta->id . " não permite alteração - Situação: " . $coleta->situacao . " - " . ColetasSituacaoType::getDescription($coleta->situacao));
                }
            }

        } catch (\Throwable $th) {
            $ret->msg = $th->getMessage();
            return $ret->toJson();
        }

        try {

            DB::beginTransaction();

            foreach ($coletas as $coleta) {
                $coletarec = $coletasrecebidas[$coleta->id];

                if (array_key_exists('motoristaid', $coletarec)) {
                    if ($coletarec['motoristaid'] > 0) {
                        $coleta->motoristaid = $coletarec['motoristaid'];
                    } else {
                        $coleta->motoristaid = null;
                    }
                }
                $coleta->updated_usuarioid = $usuario->id;
                $coleta->save();
            }

            DB::commit();

            $ret->msg = count($coletas) . ' coletas atualizadas';
            $ret->ok = true;

        } catch (\Throwable $th) {
            DB::rollBack();
            $ret->msg = $th->getMessage();
        }

        return $ret->toJson();
    }

    //baixa
    public function save_encerrar(Request $request, $id)
    {
      $ret = new RetApiController;
      try {

        $usuario = session('usuario');
        if (!$usuario) throw new Exception('Nenhum usuário autenticado');

        if (!($id>0)) throw new Exception("Nenhum ID de coleta informado");

        $coleta = Coletas::find($id);
        if (!$coleta) throw new Exception("Coleta não foi encontrada");

        if ($coleta->situacao !== ColetasSituacaoType::tcsLiberado)
            throw new Exception("Situação atual da coleta não permite baixa - Situação: " . $coleta->situacao . " - " . ColetasSituacaoType::getDescription($coleta->situacao));

        if (!($coleta->motoristaid > 0))
            throw new Exception("Nenhum motorista informado");

        if (!$request->encerramentotipo)
            throw new Exception("Tipo de baixa não informado");

        $just = isset($request->justificativa) ? $request->justificativa : '';

        $encerramentotipo = ColetasEncerramentoTipoType::fromValue((string) $request->encerramentotipo);


        $obrigatoriocfe = (($coleta->veiculoexclusico === 1) || ($coleta->cargaurgente === 1));
        if ($obrigatoriocfe) {
            $ctenumero = $request->has('ctenumero') ? intval($request->ctenumero) : null;
            if (!$ctenumero) throw new Exception("Número do CT-e é obrigatório para coleta com veículo exclusivo ou com carga urgente");
            if (!($ctenumero > 0)) throw new Exception("Número do CT-e deve ser maior do que zero");
        }

      } catch (\Throwable $th) {
        $ret->msg = $th->getMessage();
        return $ret->toJson();
      }

      try {
        DB::beginTransaction();

        $coleta->dhbaixa = Carbon::now();
        $coleta->encerramentotipo = $encerramentotipo;
        $coleta->justsituacao = $just;
        $coleta->situacao = ColetasSituacaoType::tcsEncerrado;
        $coleta->updated_usuarioid = $usuario->id;
        if ($obrigatoriocfe) $coleta->ctenumero = $ctenumero;
        $coleta->save();

        DB::commit();

        $ret->id = $coleta->id;
        $ret->data = $coleta->toObject(false);
        $ret->ok = true;

      } catch (\Throwable $th) {
        DB::rollBack();
        $ret->msg = $th->getMessage();
      }

      return $ret->toJson();
    }

    //desfazer baixa
    public function save_encerrar_desfazer(Request $request, $id)
    {
      $ret = new RetApiController;
      try {

        $usuario = session('usuario');
        if (!$usuario) throw new Exception('Nenhum usuário autenticado');

        if (!($id>0)) throw new Exception("Nenhum ID de coleta informado");
        $coleta = Coletas::find($id);
        if (!$coleta) throw new Exception("Coleta não foi encontrada");

        if ($coleta->situacao !== ColetasSituacaoType::tcsEncerrado)
            throw new Exception("Situação atual da coleta não permite baixa - Situação: " . $coleta->situacao . " - " . ColetasSituacaoType::getDescription($coleta->situacao));


        $just = isset($request->justificativa) ? $request->justificativa : '';
        if (strlen($request->justificativa) <5)
            throw new Exception("Justificativa deve conter cinco ou mais caracteres");

        if (!$request->encerramentotipo)
            throw new Exception("Tipo de baixa não informado");

        $encerramentotipo = ColetasEncerramentoTipoType::fromValue((string) $request->encerramentotipo);

      } catch (\Throwable $th) {
        $ret->msg = $th->getMessage();
        return $ret->toJson();
      }

      try {
        DB::beginTransaction();

        $coleta->dhbaixa = null;
        $coleta->encerramentotipo = $encerramentotipo;
        $coleta->situacao = ColetasSituacaoType::tcsLiberado;
        $coleta->justsituacao = $just;
        $coleta->updated_usuarioid = $usuario->id;
        $coleta->ctenumero = null;
        $coleta->save();

        DB::commit();

        $ret->id = $coleta->id;
        $ret->data = $coleta->toObject(false);
        $ret->ok = true;

      } catch (\Throwable $th) {
        DB::rollBack();
        $ret->msg = $th->getMessage();
      }

      return $ret->toJson();
    }

    //cancelar coleta
    public function cancelar(Request $request, $id)
    {
        $ret = new RetApiController;
        try {
            $usuario = session('usuario');
            if (!$usuario) throw new Exception('Nenhum usuário autenticado');

            if (!($id>0)) throw new Exception("Nenhum ID de coleta informado");
            $coleta = Coletas::find($id);
            if (!$coleta) throw new Exception("Coleta não foi encontrada");

            if (!(($coleta->situacao == ColetasSituacaoType::tcsBloqueado) || ($coleta->situacao == ColetasSituacaoType::tcsLiberado)))
                throw new Exception("Situação atual da coleta não permite cancelamento - Situação: " . $coleta->situacao . " - " . ColetasSituacaoType::getDescription($coleta->situacao));

            $just = isset($request->justificativa) ? $request->justificativa : '';
            if (strlen($request->justificativa) < 5)
                throw new Exception("Justificativa deve conter cinco ou mais caracteres");

            if (!$request->encerramentotipo)
                throw new Exception("Tipo de baixa não informado");

            $encerramentotipo = ColetasEncerramentoTipoType::fromValue((string) $request->encerramentotipo);

            // 1=interno direto, 2=interno orcamento, 3=painel do cliente, 4=Coleta avulsa aplicativo
            if (($coleta->origem === '2') && ($coleta->orcamento) && ($encerramentotipo != ColetasEncerramentoTipoType::tetReaberturaOrcamento)) {
                if ($coleta->orcamento->id > 0)
                    throw new Exception("Coleta de origem 2-Orçamento só pode ser cancelada pelo orçamento");
            }

        } catch (\Throwable $th) {
            $ret->msg = $th->getMessage();
            return $ret->toJson();
        }

        try {
            DB::beginTransaction();

            $coleta->situacao = ColetasSituacaoType::tcsCancelado;
            $coleta->encerramentotipo = $encerramentotipo;
            $coleta->dhbaixa = Carbon::now();
            $coleta->justsituacao = $just;
            $coleta->updated_usuarioid = $usuario->id;
            $coleta->save();

            DB::commit();

            $ret->id = $coleta->id;
            $ret->data = $coleta->toObject(false);
            $ret->ok = true;

        } catch (\Throwable $th) {
            DB::rollBack();
            $ret->msg = $th->getMessage();
        }

        return $ret->toJson();
    }

    //desfaz o cancelamento coleta
    public function cancelar_desfazer(Request $request, $id)
    {
        $ret = new RetApiController;
        try {

            $usuario = session('usuario');
            if (!$usuario) throw new Exception('Nenhum usuário autenticado');

            if (!($id>0)) throw new Exception("Nenhum ID de coleta informado");
            $coleta = Coletas::find($id);
            if (!$coleta) throw new Exception("Coleta não foi encontrada");

            if (!($coleta->situacao == ColetasSituacaoType::tcsCancelado))
                throw new Exception("Situação atual da coleta não permite cancelamento - Situação: " . $coleta->situacao . " - " . ColetasSituacaoType::getDescription($coleta->situacao));


            // 1=interno direto, 2=interno orcamento, 3=painel do cliente, 4=Coleta avulsa aplicativo
            if ($coleta->origem !== '1')
                throw new Exception("Somente coletas de origem 1=Interno podem ser restauradas!");


            $just = isset($request->justificativa) ? $request->justificativa : '';
            if (strlen($request->justificativa) <5)
                throw new Exception("Justificativa deve conter cinco ou mais caracteres");

            if (!$request->encerramentotipo)
                throw new Exception("Tipo de baixa não informado");

            $encerramentotipo = ColetasEncerramentoTipoType::fromValue((string) $request->encerramentotipo);
        } catch (\Throwable $th) {
            $ret->msg = $th->getMessage();
            return $ret->toJson();
        }

        try {
            DB::beginTransaction();

            $coleta->situacao = ColetasSituacaoType::tcsBloqueado;
            $coleta->dhbaixa = null;
            $coleta->encerramentotipo = $encerramentotipo;
            $coleta->encerramentotipo = null;
            $coleta->justsituacao = $just;
            $coleta->updated_usuarioid = $usuario->id;
            $coleta->save();

            DB::commit();

            $ret->id = $coleta->id;
            $ret->data = $coleta->toObject(false);
            $ret->ok = true;

        } catch (\Throwable $th) {
            DB::rollBack();
            $ret->msg = $th->getMessage();
        }

        return $ret->toJson();
    }

    public function share (Request $request)
    {
        $ret = new RetApiController;
        try {
            $usuario = session('usuario');
            $assunto = isset($request->assunto) ? $request->assunto : 'Compartilhamento de coleta';
            $mensagem = isset($request->mensagem) ? $request->mensagem : '';

            if (!isset($request->to)) throw new Exception('Nenhum e-mail informado.');
            $to = json_decode($request->to, true);

            $tolist = [];
            foreach ($to as $item) {
                $tolist[] = $item;
            }

            $cc = json_decode($request->cc, true);
            $toCC = [];
            if (isset($cc)) {
                foreach ($cc as $item) {
                    $toCC[] = $item;
                }
            }
            if (count($toCC) <= 0) $toCC = null;

            $ids = isset($request->ids) ? $request->ids : null;
            $ids = explode(",", $request->ids);
            if (!is_array($ids)) $ids[] = $ids;
            $ids = count($ids) > 0 ? $ids : null;
            if (!$ids) throw new Exception('Nenhum número de coleta informado');


            $params = [
                'ids' => implode(",", $ids),
                'output' => 'localfile'
            ];
            $request->merge($params);

            $cc = app()->make('App\Http\Controllers\api\v1\ColetasController');
            $retProcessa = app()->call([$cc, 'printOrdemColeta'], []);
            $retProcessa = (object)$retProcessa->getOriginalContent();
            if (!$retProcessa->ok)
                throw new Exception('Erro ao gerar PDF - ' . $retProcessa->msg);

            $urlfilepdf = $retProcessa->msg;
            $localfilepdf = $retProcessa->data;

            // $pdf->save($disk->path($file));
            // if (!$disk->exists('temp/' . $filename))
            //     throw new Exception('Falha ao gerar PDF. Arquivo não foi encontrado no disco.');

            $dados = [
                'to' => $tolist,
                'cc' => $toCC,
                'assunto' => $assunto,
                'mensagem' => $mensagem,
                'anexos'=> [
                    $localfilepdf
                ],
                'links' => [
                    $urlfilepdf
                ]
            ];

            SendMailPadraoJob::dispatch($dados, $usuario);

            try {
                DB::beginTransaction();


                foreach ($ids as $n) {
                    $datajson = [
                        'emailpara' => $tolist,
                        'assunto' => $assunto,
                        'mensagem' => $mensagem,
                        'anexos' => [
                            $localfilepdf
                        ],
                    ];

                    $logevento = new ColetasEventos();
                    $logevento->created_at = Carbon::now();
                    if ($usuario) $logevento->created_usuarioid = $usuario->id;
                    $logevento->datajson  = json_encode($datajson);
                    // $logevento->created_motoristaid = $motorista->id;
                    $logevento->coletaid = $n;
                    $logevento->ip = getIp();
                    $logevento->detalhe  = 'Compartilhamento de coleta por e-mail';
                    $logevento->tipo  = 'info';
                    $ins = $logevento->save();
                    if (!$ins) throw new Exception("Log de evento não foi inserido");
                }

                DB::commit();
            } catch (\Throwable $th) {
                DB::rollBack();
                $ret->msg = $th->getMessage();
            }


            $ret->ok = true;
            $ret->msg = 'E-mail enviado com sucesso!';
            return $ret->toJson();

        } catch (\Throwable $th) {
            $ret->msg = $th->getMessage();
            return $ret->toJson();
        }
    }

    public function printOrdemColeta (Request $request)
    {
        $ret = new RetApiController;
        try {
            $disk = Storage::disk('public');

            $output = isset($request->output) ? $request->output : '';

            $ids = isset($request->ids) ? $request->ids : null;
            $ids = explode(",", $ids);
            if (!is_array($ids)) $ids[] = $ids;
            $ids = count($ids) > 0 ? $ids : null;


            if (!$ids) throw new Exception('Nenhum número de coleta informado');

            $coletas = Coletas::whereIn('id', $ids)->get();
            if (!$coletas) throw new Exception('Nenhum número de coleta informado');
            if ($coletas->isEmpty()) throw new Exception('Nenhuma coleta encontrada com os dados fornecidos');

            $infoprintprodperigosos = \App\auxiliares\Helper::getConfig('coleta_info_printprodperigosos', '');

            $path = $disk->path('temp');
            if (!$disk->exists('temp')) $disk->makeDirectory('temp');

            $html = view('pdf.coletas.ordemcoleta', compact('coletas', 'infoprintprodperigosos'))->render();
            $pdf = PDF::loadHtml($html);
            $filename = 'ordemcoleta-' . md5($html) . '.pdf';

            $file = 'temp/' . $filename;

            if (!$disk->exists($file)) $disk->delete($file);
            $pdf->save($disk->path($file));

            if (!$disk->exists('temp/' . $filename))
                throw new Exception('Falha ao gerar PDF. Arquivo não foi encontrado no disco.');

            $ret->ok = true;
            $ret->msg = $disk->url($file);
            if ($output == 'localfile') {
                $ret->data = $file;
            }
            return $ret->toJson();

        } catch (\Throwable $th) {
            $ret->msg = $th->getMessage();
            return $ret->toJson();
        }
    }


    public function print_listagem (Request $request)
    {
        $ret = new RetApiController;
        try {
            $disk = Storage::disk('public');
            $usuario = session('usuario');
            $output = mb_strtolower(isset($request->output) ? $request->output : 'pdf');
            if (!(($output === 'csv') || ($output === 'xlsx'))) $output = 'xlsx';

            // copia do list
            $sortby = isset($request->sortby) ? $request->sortby : 'coletas.dhcoleta';
        $descending = isset($request->descending) ? $request->descending : 'asc';
        $dhcoletai = isset($request->dhcoletai) ? $request->dhcoletai : null;
        $dhcoletaf = isset($request->dhcoletaf) ? $request->dhcoletaf : null;
        $dhbaixai = isset($request->dhbaixai) ? $request->dhbaixai : null;
        $dhbaixaf = isset($request->dhbaixaf) ? $request->dhbaixaf : null;
        $produtosperigosos = isset($request->produtosperigosos) ? $request->produtosperigosos : null;
        $cargaurgente = isset($request->cargaurgente) ? $request->cargaurgente : null;
        $veiculoexclusico = isset($request->veiculoexclusico) ? $request->veiculoexclusico : null;
        $semmotorista = isset($request->semmotorista) ? $request->semmotorista : null;

        $pesoi = isset($request->pesoi) ? floatval($request->pesoi) : null;
        $pesof = isset($request->pesof) ? floatval($request->pesof) : null;

        $ctenumero2 = null;
        if ($request->has('ctenumero2')) {
            $ctenumero2 = json_decode($request->ctenumero2);
            if (!is_array($ctenumero2)) $ctenumero2[] = $ctenumero2;
            $ctenumero2 = count($ctenumero2) > 0 ? $ctenumero2 : null;
        }

        $ctenumero = isset($request->ctenumero) ? intval($request->ctenumero) : null;

        $ctenumero2 = null;
        $ctenumero2vazio = false;
        $ctenumero2naovazio = false;
        if ($request->has('ctenumero2')) {
            $list = json_decode($request->ctenumero2);
            $ctenumero2 = [];
            foreach ($list as $value) {
                if ($value === 'vazio') {
                    $ctenumero2vazio = true;
                } else if ($value === 'naovazio') {
                    $ctenumero2naovazio = true;
                } else {
                    $ctenumero2[] = $value;
                }
            }
            if (!(count($ctenumero2) > 0)) $ctenumero2 = null;
        }


        $clienteorigemstr = isset($request->clienteorigemstr) ? $request->clienteorigemstr : null;
        $motoristastr = isset($request->motoristastr) ? $request->motoristastr : null;
        $regiaostr = isset($request->regiaostr) ? $request->regiaostr : null;
        $enderecocoletastr = isset($request->enderecocoletastr) ? $request->enderecocoletastr : null;
        $clientedestinostr = isset($request->clientedestinostr) ? $request->clientedestinostr : null;
        $cidadedestinostr = isset($request->cidadedestinostr) ? $request->cidadedestinostr : null;

        $find = isset($request->find) ? utf8_decode($request->find) : null;
        $situacao = null;
        if (isset($request->situacao)) {
            $situacao = explode(",", $request->situacao);
            if (!is_array($situacao)) $situacao[] = $situacao;
            $situacao = count($situacao) > 0 ? $situacao : null;
        }
        $origem = null;
        if (isset($request->origem)) {
            $origem = explode(",", $request->origem);
            if (!is_array($origem)) $origem[] = $origem;
            $origem = count($origem) > 0 ? $origem : null;
        }
        $motoristas = null;
        if (isset($request->motoristas)) {
            $motoristas = explode(",", $request->motoristas);
            if (!is_array($motoristas)) $motoristas[] = $motoristas;
            $motoristas = count($motoristas) > 0 ? $motoristas : null;
        }

        $clienteorigem = null;
        if (isset($request->clienteorigem)) {
            $clienteorigem = explode(",", $request->clienteorigem);
            if (!is_array($clienteorigem)) $clienteorigem[] = $clienteorigem;
            $clienteorigem = count($clienteorigem) > 0 ? $clienteorigem : null;
        }

        $clientedestino = null;
        if (isset($request->clientedestino)) {
            $clientedestino = explode(",", $request->clientedestino);
            if (!is_array($clientedestino)) $clientedestino[] = $clientedestino;
            $clientedestino = count($clientedestino) > 0 ? $clientedestino : null;
        }

        $regiao = null;
        if (isset($request->regiao)) {
            $regiao = explode(",", $request->regiao);
            if (!is_array($regiao)) $regiao[] = $regiao;
            $regiao = count($regiao) > 0 ? $regiao : null;
        }

        $cidadedestino = null;
        if (isset($request->cidadedestino)) {
            $cidadedestino = explode(",", $request->cidadedestino);
            if (!is_array($cidadedestino)) $cidadedestino[] = $cidadedestino;
            $cidadedestino = count($cidadedestino) > 0 ? $cidadedestino : null;
        }
        $cidades = null;
        if (isset($request->cidades)) {
            $cidades = explode(",", $request->cidades);
            if (!is_array($cidades)) $cidades[] = $cidades;
            $cidades = count($cidades) > 0 ? $cidades : null;
        }

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

        // se existir numero, cancela outros filtros
        $numero = isset($request->numero) ? intVal($request->numero) : null;
        if ($numero) {
            if (!($numero>0)) $numero = null;

            if ($numero>0) {
                $dhcoletaf = null;
                $dhcoletaf = null;
                $dhbaixai = null;
                $dhbaixaf = null;
                $situacao = null;
                $origem = null;
                $find  = null;
            }
        } else {
            if ($find != '') {
                $n = intval($find);
                if ($n > 0) $numero = $n;
            }
        }

        $perpage = isset($request->perpage) ? $request->perpage : 25;
        $query = Coletas::select(DB::raw('coletas.*'))
                    ->leftJoin('cliente as clienteorigem', 'coletas.origemclienteid', '=', 'clienteorigem.id')
                    ->leftJoin('cliente as clientedestino', 'coletas.destinoclienteid', '=', 'clientedestino.id')
                        ->leftJoin('cidades as cidadedestino', 'clientedestino.cidadeid', '=', 'cidadedestino.id')
                    ->leftJoin('cidades as cidadecoleta', 'coletas.endcoleta_cidadeid', '=', 'cidadecoleta.id')
                    ->leftJoin('motorista', 'coletas.motoristaid', '=', 'motorista.id')
                    ->with( 'motorista', 'created_usuario', 'updated_usuario', 'clienteorigem', 'clientedestino', 'coletacidade', 'coletaregiao', 'orcamento', 'itens' )
                    ->when($find, function ($query) use ($find) {
                      return $query->where(function($query2) use ($find) {
                        return $query2->where('coletas.chavenota', 'like', '%'.$find.'%')
                                ->orWhere('coletas.gestaocliente_itenscomprador', 'like', '%'.$find.'%')
                                ->orWhere('coletas.gestaocliente_comprador', 'like','%'. $find.'%')
                                ->orWhere('coletas.contatonome', 'like','%'. $find.'%')
                                ->orWhere('coletas.contatoemail', 'like', '%'.$find.'%')
                                ->orWhere('coletas.obs', 'like','%'. $find.'%')
                                ->orWhere('coletas.endcoleta_cep', 'like', '%'.$find.'%')

                                ->orWhere('cidadecoleta.cidade', 'like', '%'.$find.'%')
                                ->orWhere('cidadecoleta.estado', 'like', '%'.$find.'%')
                                ->orWhere('cidadecoleta.uf', 'like','%'. $find.'%')

                                ->orWhere('motorista.nome', 'like', '%'.$find.'%')
                                ->orWhere('motorista.apelido', 'like','%'. $find.'%')

                                ->orWhere('clienteorigem.razaosocial', 'like', '%'.$find.'%')
                                ->orWhere('clienteorigem.fantasia', 'like', '%'.$find.'%')
                                ->orWhere('clienteorigem.cnpj', 'like','%'. $find.'%')
                                ->orWhere('clientedestino.razaosocial', 'like', '%'.$find.'%')
                                ->orWhere('clientedestino.fantasia', 'like', '%'.$find.'%')
                                ->orWhere('clientedestino.cnpj', 'like', '%'.$find.'%')
                                ;
                      });
                    })
                    ->when(isset($request->clienteorigemstr) && ($clienteorigemstr ? $clienteorigemstr !== '' : false), function ($query) use ($clienteorigemstr)  {
                        return $query->where(function($query2) use ($clienteorigemstr) {
                            return $query2->where('clienteorigem.razaosocial', 'like', '%'.$clienteorigemstr.'%')
                                ->orWhere('clienteorigem.fantasia', 'like', '%'.$clienteorigemstr.'%');
                        });
                    })
                    ->when(isset($request->clientedestinostr) && ($clientedestinostr ? $clientedestinostr !== '' : false), function ($query) use ($clientedestinostr)  {
                        return $query->where(function($query2) use ($clientedestinostr) {
                            return $query2->where('clientedestino.razaosocial', 'like', '%'.$clientedestinostr.'%')
                            ->orWhere('clientedestino.fantasia', 'like', '%'.$clientedestinostr.'%');
                        });
                    })
                    ->when(isset($request->motoristastr) && ($motoristastr ? $motoristastr !== '' : false), function ($query) use ($motoristastr)  {
                        return $query->where(function($query2) use ($motoristastr) {
                            return $query2->where('motorista.nome', 'like', '%'.$motoristastr.'%')
                            ->orWhere('motorista.apelido', 'like', '%'.$motoristastr.'%');
                        });
                    })

                    ->when(isset($request->enderecocoletastr) && ($enderecocoletastr ? $enderecocoletastr !== '' : false), function ($query) use ($enderecocoletastr)  {
                        return $query->where(function($query2) use ($enderecocoletastr) {
                            return $query2->where('cidadecoleta.cidade', 'like', '%'.$enderecocoletastr.'%')
                            ->orWhere('cidadecoleta.uf', 'like', '%'.$enderecocoletastr.'%');
                        });
                    })

                    ->when(isset($request->cidadedestinostr) && ($cidadedestinostr ? $cidadedestinostr !== '' : false), function ($query) use ($cidadedestinostr)  {
                        return $query->where(function($query2) use ($cidadedestinostr) {
                            return $query2->where('cidadedestino.cidade', 'like', '%'.$cidadedestinostr.'%')
                            ->orWhere('cidadedestino.uf', 'like', '%'.$cidadedestinostr.'%');
                        });
                    })

                    ->when(isset($request->ctenumero) && ($ctenumero > 0) , function ($query) use ($ctenumero)  {
                        return $query->where('coletas.ctenumero', '=', $ctenumero);
                    })
                    ->when(isset($request->ctenumero2) && ($ctenumero2 ? count($ctenumero2) > 0 : false) , function ($query) use ($ctenumero2)  {
                        return $query->whereIn('coletas.ctenumero', $ctenumero2);
                    })
                    ->when(isset($request->ctenumero2) && ($ctenumero2vazio), function ($query) {
                        return $query->whereRaw('ifnull(coletas.ctenumero,"") = ""');
                    })
                    ->when(isset($request->ctenumero2) && ($ctenumero2naovazio), function ($query) {
                        return $query->whereRaw('ifnull(coletas.ctenumero,"") <> ""');
                    })


                    ->when(isset($request->regiaostr) && ($regiaostr ? $regiaostr !== '' : false), function ($query) use ($regiaostr)  {
                        return $query->Where('cidadecoleta.regiaoid', intval($regiaostr));
                    })
                    ->when($numero, function ($query, $numero) {
                        return $query->Where('coletas.id', $numero);
                    })
                    ->when(isset($request->situacao) && ($situacao != null), function ($query, $t) use ($situacao) {
                        return $query->WhereIn('coletas.situacao', $situacao);
                    })
                    ->when(isset($request->origem) && ($origem != null), function ($query, $t) use ($origem) {
                        return $query->WhereIn('coletas.origem', $origem);
                    })
                    ->when(isset($request->motoristas) && ($motoristas != null), function ($query, $t) use ($motoristas) {
                        return $query->WhereIn('coletas.motoristaid', $motoristas);
                    })
                    ->when(isset($request->regiao) && ($regiao != null), function ($query, $t) use ($regiao) {
                        return $query->WhereIn('cidadecoleta.regiaoid', $regiao);
                    })
                    ->when(isset($request->cidades) && ($cidades != null), function ($query, $t) use ($cidades) {
                        return $query->WhereIn('coletas.endcoleta_cidadeid', $cidades);
                    })
                    ->when(isset($request->cidadedestino) && ($cidadedestino != null), function ($query, $t) use ($cidadedestino) {
                        return $query->WhereIn('clientedestino.cidadeid', $cidadedestino);
                    })
                    ->when(isset($request->clientedestino) && ($clientedestino != null), function ($query, $t) use ($clientedestino) {
                        return $query->WhereIn('clientedestino.id', $clientedestino);
                    })
                    ->when(isset($request->clienteorigem) && ($clienteorigem != null), function ($query, $t) use ($clienteorigem) {
                        return $query->WhereIn('coletas.origemclienteid', $clienteorigem);
                    })
                    ->when(isset($request->produtosperigosos), function ($query, $t) use ($produtosperigosos) {
                        return $query->Where('coletas.produtosperigosos', '=', toBool($produtosperigosos) ? 1 : 0);
                    })
                    ->when(isset($request->cargaurgente), function ($query, $t) use ($cargaurgente) {
                        return $query->Where('coletas.cargaurgente', '=', toBool($cargaurgente) ? 1 : 0);
                    })
                    ->when(isset($request->veiculoexclusico), function ($query, $t) use ($veiculoexclusico) {
                        return $query->Where('coletas.veiculoexclusico', '=', toBool($veiculoexclusico) ? 1 : 0);
                    })
                    ->when(isset($request->pesoi), function ($query) use ($pesoi) {
                        return $query->Where('coletas.peso', '>=', $pesoi);
                    })
                    ->when(isset($request->pesof), function ($query) use ($pesof) {
                        return $query->Where('coletas.peso', '<=', $pesof);
                    })
                    ->when(isset($request->dhcoletai), function ($query) use ($dhcoletai) {
                        return $query->Where(DB::Raw('date(coletas.dhcoleta)'), '>=', $dhcoletai);
                    })
                    ->when(isset($request->dhcoletaf), function ($query) use ($dhcoletaf) {
                        return $query->Where(DB::Raw('date(coletas.dhcoleta)'), '<=', $dhcoletaf);
                    })
                    ->when(isset($request->dhbaixai), function ($query) use ($dhbaixai) {
                        return $query->Where(DB::Raw('date(coletas.dhbaixa)'), '>=', $dhbaixai);
                    })
                    ->when(isset($request->dhbaixaf), function ($query) use ($dhbaixaf) {
                        return $query->Where(DB::Raw('date(coletas.dhbaixa)'), '<=', $dhbaixaf);
                    })
                    ->when(isset($request->semmotorista), function ($query) use ($semmotorista) {
                        return $query->whereRaw('if(?=1, coletas.motoristaid is null, false)', [$semmotorista]);
                    })
                    ->when($request->orderby, function ($query) use ($orderby) {
                        foreach ($orderby as $key => $value) {
                            $query->orderByRaw($key  . ' ' . $value);
                        }
                        return $query;
                    });


            // copia do list

            $count = $query->count();
            if ($count > 5000) throw new Exception('Limite de 5000 registros foi excedido. Informe os filtros para limitar a consulta.');

            $rows = $query->get();
            if (!$rows) throw new Exception('Nenhum registro encontrado');
            if (count($rows) == 0) throw new Exception('Nenhum registro encontrado');
            if (count($rows) > 5000) throw new Exception('Limite de 5000 registros foi excedido. Informe os filtros para limitar a consulta.');

                // impressão indisponivel
            if ($output == 'pdf') {
                throw new Exception('Versão em PDF indisponível');

                // $path = $disk->path('temp');
                // if (!$disk->exists('temp')) $disk->makeDirectory('temp');

                // $html = view('pdf.notaconferencia.listagem', compact('rows', 'usuario'))->render();

                // $pdf = PDF::loadHtml($html);
                // $pdf->setPaper('A4', 'landscape');
                // $filename = 'notaconferencia-listagem-' . md5($html) . '.pdf';

                // $file = 'temp/' . $filename;

                // if (!$disk->exists($file)) $disk->delete($file);
                // $pdf->save($disk->path($file));

                // if (!$disk->exists('temp/' . $filename))
                //     throw new Exception('Falha ao gerar PDF. Arquivo não foi encontrado no disco.');

                // if ($output == 'teste') {
                //     return $disk->download('temp/' . $filename, $filename, [
                //         'Content-Type' => 'application/pdf',
                //         'Content-Disposition' => 'inline; filename="'.$filename.'"'
                //     ]);
                // }

                // $ret->ok = true;
                // $ret->msg = $disk->url($file);
            } else {
                $ret->msg = self::exportFile($rows, $output);
                $ret->ok = true;
            }
            return $ret->toJson();
        } catch (\Throwable $th) {
            $ret->msg = $th->getMessage();
            return $ret->toJson();
        }
    }

    public function exportFile($dataset, $format) {
        try {
            $format = mb_strtolower($format);
            if (!(($format=='xlsx') || ($format=='csv') || ($format=='xls')))
                throw new Exception('Formato inválido. Permitido somente XLSX, XLS, CSV');

            $path = 'export/' . Carbon::now()->format('Y-m-d') . '/';
            $filename = 'coletas-' . Carbon::now()->format('Y-m-d-H-i-s-') . md5(createRandomVal(5) . Carbon::now()) . '.' . $format;
            $fullfilename = '';
            ini_set('memory_limit', '-1');
            $export = new ColetasExport($dataset);
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

}
