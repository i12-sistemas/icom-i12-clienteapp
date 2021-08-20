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
use PDF;
use App\Http\Controllers\RetApiController;

use App\Models\Manutencao;
use App\Models\ManutencaoServicos;
use App\Models\Veiculo;
use App\Models\VeiculoKm;

class ManutencaoController extends Controller
{
    public function list(Request $request)
    {

      $ret = new RetApiController;
      try {

        $sortby = isset($request->sortby) ? $request->sortby : 'manutencao.created_at';
        $descending = isset($request->descending) ? $request->descending : 'desc';
        $created_ati = isset($request->created_ati) ? $request->created_ati : null;
        $created_atf = isset($request->created_atf) ? $request->created_atf : null;
        // $dhcoletai = isset($request->dhcoletai) ? $request->dhcoletai : null;
        // $dhcoletaf = isset($request->dhcoletaf) ? $request->dhcoletaf : null;
        // $produtosperigosos = isset($request->produtosperigosos) ? $request->produtosperigosos : null;
        // $cargaurgente = isset($request->cargaurgente) ? $request->cargaurgente : null;
        $veiculoid = isset($request->veiculoid) ? intval($request->veiculoid) : null;
        $veiculo = isset($request->veiculo) ? cleanDocMask(utf8_decode($request->veiculo)) : null;
        $servico = isset($request->servico) ? trim(utf8_decode($request->servico)) : null;
        $filter = isset($request->filter) ? trim(utf8_decode($request->filter)) : null;
        $codpeca = isset($request->codpeca) ? trim(utf8_decode($request->codpeca)) : null;
        // $situacao = null;
        // if (isset($request->situacao)) {
        //     $situacao = explode(",", $request->situacao);
        //     if (!is_array($situacao)) $situacao[] = $situacao;
        //     $situacao = count($situacao) > 0 ? $situacao : null;
        // }

        // // se existir numero, cancela outros filtros
        // $numero = isset($request->numero) ? intVal($request->numero) : null;
        // if ($numero) {
        //     if (!($numero>0)) $numero = null;

        //     if ($numero>0) {
        //         $dhcoletaf = null;
        //         $dhcoletai = null;
        //         $situacao = null;
        //         $find  = null;
        //     }
        // } else {
        //     if ($find != '') {
        //         $n = intval($find);
        //         if ($n > 0) $numero = $n;
        //     }
        // }

        $orderby = null;
        if (isset($request->orderby)) {
            $orderby = json_decode($request->orderby,true);
            $orderbynew = [];
            foreach ($orderby as $key => $value) {
                if ($key == 'veiculo') {
                    $lKey = 'trim(veiculo.placa)';
                } else if ($key == 'servico') {
                    $lKey = 'trim(manutencaoservicos.descricao)';
                } else if ($key == 'motorista') {
                    $lKey = 'trim(motorista.nome)';
                } else if ($key == 'regiao') {
                    $lKey = 'cidadecoleta.regiaoid';
                } else if ($key == 'enderecocoleta') {
                    $lKey = 'concat(cidadecoleta.cidade,cidadecoleta.uf)';
                } else if ($key == 'cidadedestino') {
                    $lKey = 'concat(cidadedestino.cidade,cidadedestino.uf)';
                } else {
                    $lKey = 'manutencao.' . $key;

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
        $dataset = Manutencao::select(DB::raw('manutencao.*'))
                    ->leftJoin('veiculo', 'manutencao.veiculoid', '=', 'veiculo.id')
                    ->leftJoin('manutencaoservicos', 'manutencao.servicoid', '=', 'manutencaoservicos.id')
                    ->when(isset($request->filter) && ($filter !== ''), function ($query) use ($filter) {
                      return $query->where(function($query2) use ($filter) {
                        return $query2->Where('manutencao.codpeca', 'like', '%'.$filter.'%')
                                ->orWhere('manutencao.obs', 'like', '%'.$filter.'%')
                                ->orWhere('veiculo.placa', 'like', '%'.cleanDocMask($filter).'%')
                                ->orWhere('manutencaoservicos.descricao', 'like', '%'.$filter.'%')
                                ;
                      });
                    })
                    ->when(isset($request->codpeca) && ($codpeca !== ''), function ($query) use ($codpeca) {
                        return $query->Where('manutencao.codpeca', 'like', '%'.$codpeca.'%');
                    })
                    ->when(isset($request->servico) && ($servico !== ''), function ($query) use ($servico) {
                        return $query->Where('manutencaoservicos.descricao', 'like', '%'.$servico.'%');
                    })
                    ->when(isset($request->veiculo) && ($veiculo !== ''), function ($query) use ($veiculo) {
                        return $query->Where('veiculo.placa', 'like', '%'.$veiculo.'%');
                    })
                    ->when(isset($request->veiculoid), function ($query, $t) use ($veiculoid) {
                        return $query->Where('manutencao.veiculoid', '=', $veiculoid);
                    })
                    ->when(isset($request->created_ati), function ($query) use ($created_ati) {
                        return $query->Where(DB::Raw('date(manutencao.created_at)'), '>=', $created_ati);
                    })
                    ->when(isset($request->created_atf), function ($query) use ($created_atf
                    ) {
                        return $query->Where(DB::Raw('date(manutencao.created_at)'), '<=', $created_atf);
                    })
                    ->when($request->orderby, function ($query) use ($orderby) {
                        foreach ($orderby as $key => $value) {
                            $query->orderByRaw($key  . ' ' . $value);
                        }
                        return $query;
                    })
                    ->orderby('manutencao.id', 'desc')
                    ->paginate($perpage);
        $dados = [];
        foreach ($dataset as $manut) {
            $dados[] = $manut->toObject(true);//showCompact
            // $dados[] = $coleta->toArray();//showCompact
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

    public function agenda(Request $request)
    {

      $ret = new RetApiController;
      try {
        $sortby = isset($request->sortby) ? $request->sortby : 'manutencao.created_at';
        $descending = isset($request->descending) ? $request->descending : 'desc';
        $situacaoalerta = isset($request->situacaoalerta) ? $request->situacaoalerta : '';

        $veiculoid = null;
        if (isset($request->veiculoid)) {
            $veiculoid = explode(",", $request->veiculoid);
            if (!is_array($veiculoid)) $veiculoid[] = $veiculoid;
            $veiculoid = count($veiculoid) > 0 ? $veiculoid : null;
        }
        $servicoid = null;
        if (isset($request->servicoid)) {
            $servicoid = explode(",", $request->servicoid);
            if (!is_array($servicoid)) $servicoid[] = $servicoid;
            $servicoid = count($servicoid) > 0 ? $servicoid : null;
        }

        $find = isset($request->find) ? utf8_decode($request->find) : null;



        $orderby = null;
        if (isset($request->orderby)) {
            $orderby = json_decode($request->orderby,true);
            $orderbynew = [];
            foreach ($orderby as $key => $value) {
                if ($key == 'placa') {
                    $lKey = 'trim(veiculo.placa)';
                } else if ($key == 'servico') {
                    $lKey = 'trim(manutencaoservicos.descricao)';
                } else {
                    $lKey = 'manutencao.' . $key;

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
        $dataset = Manutencao::select(DB::raw('manutencao.*'))
                    ->where('manutencao.realizado', '=', 0)
                    ->leftJoin('veiculo', 'manutencao.veiculoid', '=', 'veiculo.id')
                    ->leftJoin('manutencaoservicos', 'manutencao.servicoid', '=', 'manutencaoservicos.id')
                    ->with('veiculo', 'servico', 'created_usuario', 'updated_usuario')
                    ->when($find, function ($query) use ($find) {
                      return $query->where(function($query2) use ($find) {
                        return $query2->Where('manutencao.codpeca', 'like', $find.'%')
                                ->orWhere('manutencao.obs', 'like', $find.'%')

                                ->orWhere('veiculo.placa', 'like', $find.'%')
                                ->orWhere('veiculo.descricao', 'like', $find.'%')

                                ->orWhere('manutencaoservicos.descricao', 'like', $find.'%')
                                ;

                      });
                    })
                    ->when(isset($request->veiculoid), function ($query, $t) use ($veiculoid) {
                        return $query->WhereIn('manutencao.veiculoid', $veiculoid);
                    })
                    ->when(isset($request->servicoid), function ($query, $t) use ($servicoid) {
                        return $query->WhereIn('manutencao.servicoid', $servicoid);
                    })
                    // situação de alerta pode ser "sematraso" ou "ematraso"
                    ->when(isset($request->situacaoalerta), function ($query, $t) use ($situacaoalerta) {
                        if ($situacaoalerta == 'sematraso') {
                            return $query->WhereRaw(
                                            '(' .
                                            '((veiculo.ultimokm < manutencao.alertakm) AND (veiculo.ultimokm < manutencao.limitekm)) ' .
                                            ' AND ' .
                                            '(if(manutencao.validadedias > 0,  ((date(now()) < manutencao.limitedata) AND (date(now()) < manutencao.alertadata)), true)) ' .
                                            ')'

                                        , []);
                        } elseif ($situacaoalerta == 'ematraso') {
                            return $query->WhereRaw(
                                            '(' .
                                            '((veiculo.ultimokm >= manutencao.alertakm) OR (veiculo.ultimokm >= manutencao.limitekm)) ' .
                                            ' OR ' .
                                            '(if(manutencao.validadedias > 0,  ((date(now()) >= manutencao.limitedata) OR (date(now()) >= manutencao.alertadata)), false)) ' .
                                            ')'

                                        , []);
                        }
                    })
                    ->when($request->orderby, function ($query) use ($orderby) {
                        foreach ($orderby as $key => $value) {
                            $query->orderByRaw($key  . ' ' . $value);
                        }
                        return $query;
                    })
                    ->orderby('manutencao.id', 'desc')
                    ->paginate($perpage);
        $dados = [];
        foreach ($dataset as $manut) {
            $dados[] = $manut->toObject(true);//showCompact
            // $dados[] = $coleta->toArray();//showCompact
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


    public function dashboard1(Request $request)
    {
      $ret = new RetApiController;
      try {

        $manutencaototal = Manutencao::where('realizado', '=', 0)->count();

        $emalerta = Manutencao::leftJoin('veiculo', 'manutencao.veiculoid', '=', 'veiculo.id')
                            ->where('manutencao.realizado', '=', 0)
                            ->WhereRaw(
                                '(' .
                                '((veiculo.ultimokm >= manutencao.alertakm) OR (veiculo.ultimokm >= manutencao.limitekm)) ' .
                                ' OR ' .
                                '(if(manutencao.validadedias > 0,  ((date(now()) >= manutencao.limitedata) OR (date(now()) >= manutencao.alertadata)), false)) ' .
                                ')'

                            , [])
                            ->count();
        $numeroveiculos = Manutencao::leftJoin('veiculo', 'manutencao.veiculoid', '=', 'veiculo.id')
                            ->where('manutencao.realizado', '=', 0)
                            ->count(DB::raw('distinct veiculo.id'));


        $today = Carbon::today();
        $yesterday = Carbon::yesterday();
        // // agendado!
        $agendado_hoje = Manutencao::leftJoin('veiculo', 'manutencao.veiculoid', '=', 'veiculo.id')
            ->where('manutencao.realizado', '=', 0)
            ->WhereRaw(
                '(' .
                '((veiculo.ultimokm >= manutencao.alertakm) OR (veiculo.ultimokm >= manutencao.limitekm)) ' .
                ' OR ' .
                '(if(manutencao.validadedias > 0,  ((date(now()) >= manutencao.limitedata) OR (date(now()) >= manutencao.alertadata)), false)) ' .
                ')'

            , [])
            ->count();
        // $agendado_hoje = Coletas::where('situacao', '=', '2')->where(DB::Raw('date(dhbaixa)'), Carbon::today()->toDateString())->count();
        $agendado_estasemana = 123;
        // $agendado_estasemana = 'Coletas::where('situacao', '=', '2')->whereBetween(DB::Raw('date(dhbaixa)'), [Carbon::today()->startOfWeek()->toDateString(), Carbon::today()->toDateString()])->count()';
        $agendado_estames = 456;
        // $agendado_estames = Coletas::where('situacao', '=', '2')->whereBetween(DB::Raw('date(dhbaixa)'), [Carbon::today()->startOfMonth()->toDateString(), Carbon::today()->toDateString()])->count();

        // veiculos com manutenção ligada!
        $veic_total = Veiculo::join('veiculo_alertamanut', 'veiculo.alertamanutid', '=', 'veiculo_alertamanut.id')
            ->where('veiculo_alertamanut.revoked', '=', 0)
            ->count();

        $veic_normal = Veiculo::join('veiculo_alertamanut', 'veiculo.alertamanutid', '=', 'veiculo_alertamanut.id')
            ->where('veiculo_alertamanut.revoked', '=', 0)
            ->where('veiculo_alertamanut.prioridade', '=', '1')
            ->count();

        $veic_critica = Veiculo::join('veiculo_alertamanut', 'veiculo.alertamanutid', '=', 'veiculo_alertamanut.id')
            ->where('veiculo_alertamanut.revoked', '=', 0)
            ->where('veiculo_alertamanut.prioridade', '=', '2')
            ->count();

        $veic_obrigatoria = Veiculo::join('veiculo_alertamanut', 'veiculo.alertamanutid', '=', 'veiculo_alertamanut.id')
            ->where('veiculo_alertamanut.revoked', '=', 0)
            ->where('veiculo_alertamanut.prioridade', '=', '3')
            ->count();


        $dados = [
            'manutencao' => [
                'emalerta' => $emalerta,
                'numeroveiculos' => $numeroveiculos,
            ],
            'agendado' => [
                'total' => $manutencaototal,
                'hoje' => $agendado_hoje,
                'semana' => $agendado_estasemana,
                'mes' => $agendado_estames
            ],
            'veiculos_manutencao_ligada' => [
                'total' => $veic_total,
                'normal' => $veic_normal,
                'critica' => $veic_critica,
                'obrigatoria' => $veic_obrigatoria
            ]
        ];
        $ret->data = $dados;
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

        $dataset = Manutencao::find($find);
        if (!$dataset) throw new Exception("Manutenção não foi encontrada");

        $ret->data = $dataset->toObject(True);
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
          'veiculoid' => ['required', 'exists:veiculo,id'],
          'servicoid' => ['required', 'exists:manutencaoservicos,id'],
          'kmatual' => ['required', 'integer', 'min:1'],
          'validadedias' => ['required', 'integer', 'min:0'],
          'validadekm' => ['required', 'integer', 'min:0']
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

        $servico = ManutencaoServicos::find($request->servicoid);
        if (!$servico) throw new Exception("Nenhum serviço encontrado");
        if (!$servico->ativo) throw new Exception("Serviço inativado");

        $veiculo = Veiculo::find($request->veiculoid);
        if (!$veiculo) throw new Exception("Nenhum veículo encontrado");
        if (!$veiculo->ativo) throw new Exception("Veículo inativado");

        $ultimamanut = Manutencao::where('servicoid', $servico->id)
                                ->where('veiculoid', $veiculo->id)
                                ->orderBy('created_at', 'desc')
                                ->first();
        if ($ultimamanut) {
            if ($request->kmatual < $ultimamanut->kmatual)
            throw new Exception("Km atual não poder ser inferior o KM da última manutenção - " . $ultimamanut->kmatual . ' Km');
        }

      } catch (\Throwable $th) {
        $ret->msg = $th->getMessage();
        return $ret->toJson();
      }

      try {
        DB::beginTransaction();


        $dataset = new Manutencao();
        $dataset->created_usuarioid = $usuario->id;
        $dataset->updated_usuarioid = $usuario->id;
        $dataset->veiculoid = $veiculo->id;
        $dataset->servicoid = $servico->id;

        $dataset->codpeca = $request->codpeca;
        $dataset->obs = $request->obs;

        $dataset->kmatual = $request->kmatual;
        $dataset->validadekm = $request->validadekm;
        $dataset->limitekm = $dataset->kmatual + $dataset->validadekm;

        $manutencao_alerta_minimo_km = floatval( \App\auxiliares\Helper::getConfig('manutencao_alerta_minimo_km', 15));
        // (limitekm - (validadekm * (15/100)))
        $alertakm = ($dataset->limitekm - ($dataset->validadekm * ($manutencao_alerta_minimo_km / 100)));
        $alertakm = round($alertakm, 0, PHP_ROUND_HALF_DOWN);
        $dataset->alertakm = $alertakm;

        $dataset->validadedias = $request->validadedias;
        if ($dataset->validadedias > 0) {
            $manutencao_alerta_minimo_dias = \App\auxiliares\Helper::getConfig('manutencao_alerta_minimo_dias', 5);
            $dataset->limitedata = Carbon::now()->add($dataset->validadedias, 'day');
            $dataset->alertadata = Carbon::now()->add($dataset->validadedias, 'day')->add($manutencao_alerta_minimo_dias, 'day');
        } else {
            $dataset->limitedata = null;
            $dataset->alertadata = null;
        }

        $dataset->realizado = 0;
        $dataset->save();

        $km = new VeiculoKm();
        $km->veiculoid = $veiculo->id;
        $km->km = $request->kmatual;
        $km->tableorigem = 'manutencao';
        $km->tableid = $dataset->id;
        $km->created_at = Carbon::now();
        $km->dhleitura = Carbon::now();
        $km->created_usuarioid = $usuario->id;
        $km->save();

        Manutencao::where('servicoid', $servico->id)
                    ->where('veiculoid', $veiculo->id)
                    ->where('realizado', 0)
                    ->where('id', '<>', $dataset->id)
                    ->update([
                        'realizado' => 1,
                        'updated_at' => Carbon::now(),
                        'updated_usuarioid' => $usuario->id
                    ]);


        DB::commit();

        $ret->id = $dataset->id;
        $ret->data = $dataset->toObject(false);
        $ret->ok = true;
      } catch (\Throwable $th) {
        DB::rollBack();
        $ret->msg = $th->getMessage();
      }


      return $ret->toJson();
    }

    public function printlistagemagenda (Request $request)
    {
        $ret = new RetApiController;
        try {
            ini_set('memory_limit', '-1');
            $disk = Storage::disk('public');
            $usuario = session('usuario');

            $sortby = isset($request->sortby) ? $request->sortby : 'manutencao.created_at';
            $descending = isset($request->descending) ? $request->descending : 'desc';
            $situacaoalerta = isset($request->situacaoalerta) ? $request->situacaoalerta : '';

            $veiculoid = null;
            if (isset($request->veiculoid)) {
                $veiculoid = explode(",", $request->veiculoid);
                if (!is_array($veiculoid)) $veiculoid[] = $veiculoid;
                $veiculoid = count($veiculoid) > 0 ? $veiculoid : null;
            }
            $servicoid = null;
            if (isset($request->servicoid)) {
                $servicoid = explode(",", $request->servicoid);
                if (!is_array($servicoid)) $servicoid[] = $servicoid;
                $servicoid = count($servicoid) > 0 ? $servicoid : null;
            }

            $find = isset($request->find) ? utf8_decode($request->find) : null;

            $orderby = null;
            if (isset($request->orderby)) {
                $orderby = json_decode($request->orderby,true);
                $orderbynew = [];
                foreach ($orderby as $key => $value) {
                    if ($key == 'placa') {
                        $lKey = 'trim(veiculo.placa)';
                    } else if ($key == 'servico') {
                        $lKey = 'trim(manutencaoservicos.descricao)';
                    } else {
                        $lKey = 'manutencao.' . $key;

                    }
                    $orderbynew[$lKey] = strtoupper($value);
                }
                if (count($orderbynew) > 0) {
                    $orderby = $orderbynew;
                } else {
                    $orderby = null;
                }
            }


            $rows = Manutencao::select(DB::raw('manutencao.*'))
                    ->where('manutencao.realizado', '=', 0)
                    ->leftJoin('veiculo', 'manutencao.veiculoid', '=', 'veiculo.id')
                    ->leftJoin('manutencaoservicos', 'manutencao.servicoid', '=', 'manutencaoservicos.id')
                    ->with('veiculo', 'servico', 'created_usuario', 'updated_usuario')
                    ->when($find, function ($query) use ($find) {
                    return $query->where(function($query2) use ($find) {
                        return $query2->Where('manutencao.codpeca', 'like', $find.'%')
                                ->orWhere('manutencao.obs', 'like', $find.'%')

                                ->orWhere('veiculo.placa', 'like', $find.'%')
                                ->orWhere('veiculo.descricao', 'like', $find.'%')

                                ->orWhere('manutencaoservicos.descricao', 'like', $find.'%')
                                ;

                    });
                    })
                    ->when(isset($request->veiculoid), function ($query, $t) use ($veiculoid) {
                        return $query->WhereIn('manutencao.veiculoid', $veiculoid);
                    })
                    ->when(isset($request->servicoid), function ($query, $t) use ($servicoid) {
                        return $query->WhereIn('manutencao.servicoid', $servicoid);
                    })
                    // situação de alerta pode ser "sematraso" ou "ematraso"
                    ->when(isset($request->situacaoalerta), function ($query, $t) use ($situacaoalerta) {
                        if ($situacaoalerta == 'sematraso') {
                            return $query->WhereRaw(
                                            '(' .
                                            '((veiculo.ultimokm < manutencao.alertakm) AND (veiculo.ultimokm < manutencao.limitekm)) ' .
                                            ' AND ' .
                                            '(if(manutencao.validadedias > 0,  ((date(now()) < manutencao.limitedata) AND (date(now()) < manutencao.alertadata)), true)) ' .
                                            ')'

                                        , []);
                        } elseif ($situacaoalerta == 'ematraso') {
                            return $query->WhereRaw(
                                            '(' .
                                            '((veiculo.ultimokm >= manutencao.alertakm) OR (veiculo.ultimokm >= manutencao.limitekm)) ' .
                                            ' OR ' .
                                            '(if(manutencao.validadedias > 0,  ((date(now()) >= manutencao.limitedata) OR (date(now()) >= manutencao.alertadata)), false)) ' .
                                            ')'

                                        , []);
                        }
                    })
                    ->when($request->orderby, function ($query) use ($orderby) {
                        foreach ($orderby as $key => $value) {
                            $query->orderByRaw($key  . ' ' . $value);
                        }
                        return $query;
                    })
                    ->orderby('manutencao.id', 'desc')
                    ->get();

            $path = $disk->path('temp');
            if (!$disk->exists('temp')) $disk->makeDirectory('temp');

            $html = view('pdf.manutencoes.listagem', compact('rows', 'usuario'))->render();

            // $config = [
            //     'title' => 'Listagem de entrada de notas',
            //     'author'=> ENV('APP_NAME',''),
            //     'orientation' => 'L',
            //     'format' => 'A4',
            //     'margin_left'          => 10,
            //     'margin_right'         => 10,
            //     'margin_top'           => 10,
            //     'margin_bottom'        => 10,
            //     'margin_header'        => 0,
            //     'margin_footer'        => 0,
            // ];
            // $pdf = PDF::loadHtml($html, $config);
            $pdf = PDF::loadHtml($html);
            $pdf->setPaper('A4', 'landscape');
            $filename = 'manutencoes-listagem-agenda' . md5($html) . '.pdf';

            $file = 'temp/' . $filename;

            if (!$disk->exists($file)) $disk->delete($file);
            $pdf->save($disk->path($file));

            if (!$disk->exists('temp/' . $filename))
                throw new Exception('Falha ao gerar PDF. Arquivo não foi encontrado no disco.');

            $ret->ok = true;
            $ret->msg = $disk->url($file);
            return $ret->toJson();

            // return $disk->download('temp/' . $filename, $filename, [
            //     'Content-Type' => 'application/pdf',
            //     'Content-Disposition' => 'inline; filename="'.$filename.'"'
            // ]);

        } catch (\Throwable $th) {
            $ret->msg = $th->getMessage();
            return $ret->toJson();
        }
    }
}
