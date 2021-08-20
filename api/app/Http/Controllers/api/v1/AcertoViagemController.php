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

use App\auxiliares\Helper;

use App\Http\Controllers\RetApiController;

use PDF;

use App\Models\AcertoViagem;
use App\Models\AcertoViagemRoteiro;
use App\Models\AcertoViagemDespesas;
use App\Models\AcertoViagemPeriodo;
use App\Models\AcertoViagemAbastec;

class AcertoViagemController extends Controller
{
    public function list(Request $request)
    {
      $ret = new RetApiController;
      try {
        $createdati = isset($request->createdati) ? $request->createdati : null;
        $createdatf = isset($request->createdatf) ? $request->createdatf : null;
        $dhacertoi = isset($request->dhacertoi) ? $request->dhacertoi : null;
        $dhacertof = isset($request->dhacertof) ? $request->dhacertof : null;
        $status = isset($request->status) ? $request->status : null;
        $find = isset($request->find) ? utf8_decode($request->find) : null;

        $motorista = null;
        if (isset($request->motorista)) {
            $motorista = explode(",", $request->motorista);
            if (!is_array($motorista)) $motorista[] = $motorista;
            $motorista = count($motorista) > 0 ? $motorista : null;
        }

        $veiculo = null;
        if (isset($request->veiculo)) {
            $veiculo = explode(",", $request->veiculo);
            if (!is_array($veiculo)) $veiculo[] = $veiculo;
            $veiculo = count($veiculo) > 0 ? $veiculo : null;
        }

        $veiculocarreta = null;
        if (isset($request->veiculocarreta)) {
            $veiculocarreta = explode(",", $request->veiculocarreta);
            if (!is_array($veiculocarreta)) $veiculocarreta[] = $veiculocarreta;
            $veiculocarreta = count($veiculocarreta) > 0 ? $veiculocarreta : null;
        }

        $cidadedestino = null;
        if (isset($request->cidadedestino)) {
            $cidadedestino = explode(",", $request->cidadedestino);
            if (!is_array($cidadedestino)) $cidadedestino[] = $cidadedestino;
            $cidadedestino = count($cidadedestino) > 0 ? $cidadedestino : null;
        }

        $cidadeorigem = null;
        if (isset($request->cidadeorigem)) {
            $cidadeorigem = explode(",", $request->cidadeorigem);
            if (!is_array($cidadeorigem)) $cidadeorigem[] = $cidadeorigem;
            $cidadeorigem = count($cidadeorigem) > 0 ? $cidadeorigem : null;
        }


        $orderby = null;
        $descending = true;
        $sortby = null;
        if (isset($request->orderby)) {
            $orderby = json_decode($request->orderby,true);
            $orderbynew = [];
            foreach ($orderby as $key => $value) {
                if ($key == 'motorista') {
                    $lKey = 'trim(motorista.nome)';
                } else if ($key == 'veiculo') {
                    $lKey = 'trim(veiculo.placa)';
                } else if ($key == 'veiculocarreta') {
                    $lKey = 'trim(veiculocarreta.placa)';
                } else if ($key == 'cidadedestino') {
                    $lKey = 'concat(cidadedestino.cidade,cidadedestino.uf)';
                } else if ($key == 'cidadeorigem') {
                    $lKey = 'concat(cidadeorigem.cidade,cidadeorigem.uf)';
                } else {
                    $lKey = 'acertoviagem.' . $key;

                }
                $orderbynew[$lKey] = strtoupper($value);
                $descending = strtoupper($value) === 'DESC';
                $sortby = $key;
            }
            if (count($orderbynew) > 0) {
                $orderby = $orderbynew;
            } else {
                $orderby = null;
            }
        }

        // se existir numero, cancela outros filtros
        $id = isset($request->id) ? intVal($request->id) : null;
        if ($id) {
            if (!($id>0)) $id = null;

            if ($id>0) {
                $dhcoletaf = null;
                $dhcoletaf = null;
                $dhbaixai = null;
                $dhbaixaf = null;
                $situacao = null;
                $origem = null;
                $find  = null;
            }
        }

        $perpage = isset($request->perpage) ? $request->perpage : 25;
        $rows = AcertoViagem::select(DB::raw('acertoviagem.*'))
                    ->leftJoin('veiculo', 'acertoviagem.veiculoid', '=', 'veiculo.id')
                    ->leftJoin('veiculo as veiculocarreta', 'acertoviagem.veiculocarretaid', '=', 'veiculocarreta.id')
                    ->leftJoin('cidades as cidadeorigem', 'acertoviagem.cidadeorigemid', '=', 'cidadeorigem.id')
                    ->leftJoin('cidades as cidadedestino', 'acertoviagem.cidadedestinoid', '=', 'cidadedestino.id')
                    ->leftJoin('motorista', 'acertoviagem.motoristaid', '=', 'motorista.id')
                    ->with('cidadeorigem', 'cidadedestino', 'motorista', 'created_usuario', 'updated_usuario', 'veiculo', 'veiculocarreta', 'periodos', 'abastecimentos', 'despesas')
                    ->when($find, function ($query) use ($find) {
                      return $query->where(function($query2) use ($find) {
                        $n = intval($find);
                        return $query2->orWhere('veiculo.descricao', 'like', $find.'%')
                                ->orWhere('veiculo.placa', 'like', $find.'%')

                                ->orWhere('veiculocarreta.descricao', 'like', $find.'%')
                                ->orWhere('veiculocarreta.placa', 'like', $find.'%')

                                ->orWhere('cidadeorigem.cidade', 'like', $find.'%')
                                ->orWhere('cidadeorigem.estado', 'like', $find.'%')
                                ->orWhere('cidadeorigem.uf', 'like', $find.'%')

                                ->orWhere('cidadedestino.cidade', 'like', $find.'%')
                                ->orWhere('cidadedestino.estado', 'like', $find.'%')
                                ->orWhere('cidadedestino.uf', 'like', $find.'%')

                                ->orWhere('motorista.nome', 'like', $find.'%')
                                ->orWhere('motorista.apelido', 'like', $find.'%')

                                ->orWhereRaw('if(?>0, acertoviagem.id=?, false)', [$n, $n])
                                ;
                      });
                    })
                    ->when($id, function ($query, $id) {
                        return $query->Where('acertoviagem.id', '=', $id);
                    })
                    ->when(isset($request->motorista) && ($motorista != null), function ($query, $t) use ($motorista) {
                        return $query->WhereIn('acertoviagem.motoristaid', $motorista);
                    })
                    ->when(isset($request->veiculo) && ($veiculo != null), function ($query, $t) use ($veiculo) {
                        return $query->WhereIn('acertoviagem.veiculoid', $veiculo);
                    })
                    ->when(isset($request->veiculocarreta) && ($veiculocarreta != null), function ($query, $t) use ($veiculocarreta) {
                        return $query->WhereIn('acertoviagem.veiculocarretaid', $veiculocarreta);
                    })
                    ->when(isset($request->cidadeorigem) && ($cidadeorigem != null), function ($query, $t) use ($cidadeorigem) {
                        return $query->WhereIn('acertoviagem.cidadeorigemid', $cidadeorigem);
                    })
                    ->when(isset($request->cidadedestino) && ($cidadedestino != null), function ($query, $t) use ($cidadedestino) {
                        return $query->WhereIn('acertoviagem.cidadedestinoid', $cidadedestino);
                    })
                    ->when(isset($request->status), function ($query, $t) use ($status) {
                        return $query->Where('acertoviagem.status', '=', toBool($status) ? 1 : 0);
                    })
                    ->when($request->createdati, function ($query) use ($createdati) {
                        return $query->Where(DB::Raw('date(acertoviagem.created_at)'), '>=', $createdati);
                    })
                    ->when($request->createdatf, function ($query) use ($createdatf) {
                        return $query->Where(DB::Raw('date(acertoviagem.created_at)'), '<=', $createdatf);
                    })
                    ->when($request->dhacertoi, function ($query) use ($dhacertoi) {
                        return $query->Where(DB::Raw('date(acertoviagem.dhacerto)'), '>=', $dhacertoi);
                    })
                    ->when($request->dhacertof, function ($query) use ($dhacertof) {
                        return $query->Where(DB::Raw('date(acertoviagem.dhacerto)'), '<=', $dhacertof);
                    })
                    ->when($request->orderby, function ($query) use ($orderby) {
                        foreach ($orderby as $key => $value) {
                            $query->orderByRaw($key  . ' ' . $value);
                        }
                        return $query;
                    })
                    ->paginate($perpage);
        $dados = [];
        foreach ($rows as $row) {
            $dados[] = $row->export(false);
        }
        $ret->data = $dados;
        $ret->sortby = $sortby;
        $ret->descending = ($descending ? 'desc' : 'asc');
        $ret->collection = $rows;
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

        $row = AcertoViagem::find($find);
        if (!$row) throw new Exception("Nenhum acerto encontrado");

        $ret->data = $row->export(true);
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
            'motoristaid' => ['required', 'exists:motorista,id'],
            'veiculoid' => ['required', 'exists:veiculo,id'],
            'cidadeorigemid' => ['required', 'exists:cidades,id'],
            'cidadedestinoid' => ['required', 'exists:cidades,id'],
            'kmfim' => ['required', 'integer', 'min:0'],
            'kmini' => ['required', 'integer', 'min:0'],
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
            $dataset = AcertoViagem::find($id);
            if (!$dataset) throw new Exception("Nenhum acerto encontrado");
            if ($dataset->status === 1) throw new Exception("Acerto de viagem foi encerrado");
        }

        $param_limites = [
            'cafe' => Carbon::createFromFormat( 'H:i', \App\auxiliares\Helper::getConfig('acerto_cafe_horario', '00:00')),
            'almoco' => Carbon::createFromFormat( 'H:i', \App\auxiliares\Helper::getConfig('acerto_almoco_horario', '00:00')),
            'jantar' => Carbon::createFromFormat( 'H:i', \App\auxiliares\Helper::getConfig('acerto_jantar_horario', '00:00')),
            'pernoite' => Carbon::createFromFormat( 'H:i', \App\auxiliares\Helper::getConfig('acerto_pernoite_horario', '00:00')),

            'cafe_vlr' => \App\auxiliares\Helper::getConfig('acerto_cafe_vlr', 0),
            'almoco_vlr' => \App\auxiliares\Helper::getConfig('acerto_almoco_vlr', 0),
            'jantar_vlr' => \App\auxiliares\Helper::getConfig('acerto_jantar_vlr', 0),
            'pernoite_vlr' =>\App\auxiliares\Helper::getConfig('acerto_pernoite_vlr', 0),
        ];


      } catch (\Throwable $th) {
        $ret->msg = $th->getMessage();
        return $ret->toJson();
      }

      try {
        DB::beginTransaction();



        if ($action=='add') {
            $dataset = new AcertoViagem();
            $dataset->created_usuarioid = $usuario->id;
            $dataset->status = 0;
        }

        $forceupdatevalorcalculo = isset($request->forceupdatevalorcalculo) ? boolVal($request->forceupdatevalorcalculo) : false;
        if (($action == 'add') || ($forceupdatevalorcalculo)) {
            $dataset->cafevlr = $param_limites["cafe_vlr"];
            $dataset->almocovlr = $param_limites['almoco_vlr'];
            $dataset->jantarvlr = $param_limites['jantar_vlr'];
            $dataset->pernoitevlr = $param_limites['pernoite_vlr'];


            $dataset->cafehrbc = $param_limites["cafe"]->format('H:i') . ':00';
            $dataset->almocohrbc = $param_limites['almoco']->format('H:i') . ':00';
            $dataset->jantarhrbc = $param_limites['jantar']->format('H:i') . ':00';
            $dataset->pernoitehrbc = $param_limites['pernoite']->format('H:i') . ':00';
        }

        $dataset->updated_usuarioid = $usuario->id;
        $dataset->motoristaid = $request->motoristaid;
        $dataset->veiculoid = $request->veiculoid;
        $dataset->veiculocarretaid = $request->has('veiculocarretaid') ? $request->veiculocarretaid : null  ;
        $dataset->cidadeorigemid = $request->cidadeorigemid;
        $dataset->cidadedestinoid = $request->cidadedestinoid;

        $dataset->kmini = intVal($request->kmini);
        $dataset->kmfim = intVal($request->kmfim);
        $dataset->kmtotal = intVal($dataset->kmfim - $dataset->kmini);

        if (isset($request->vlradicional)) $dataset->vlradicional = $request->vlradicional;
        if (isset($request->vlradiantamento)) $dataset->vlradiantamento = $request->vlradiantamento;

        $dataset->vlradiantamentototal = $dataset->vlradicional + $dataset->vlradiantamento;

        $dataset->save();

        // roteiro
        if (isset($request->roteiro)) {
            foreach ($request->roteiro as $rota) {
                $rota  =(object)$rota;
                $rota->item  =(object)$rota->item;
                if ($rota->action == 'delete') {
                    $del = AcertoViagemRoteiro::find($rota->item->id)->delete();
                    if (!$del) throw new Exception("Rota não foi excluída - " . $rota->item->rota);
                }
                if ($rota->action == 'update') {
                    $item = AcertoViagemRoteiro::find($rota->item->id);
                    if ($item) {
                        $item->rota = $rota->item->rota;
                        $item->cidadeid = $rota->item->cidadeid;
                        $item->ordem = $rota->item->ordem;
                        $ins = $item->save();
                        if (!$ins) throw new Exception("Rota não foi atualizado - " . $rota->item->rota);
                    }
                }
                if ($rota->action == 'insert') {
                    $item = new AcertoViagemRoteiro();
                    $item->acertoid = $dataset->id;
                    $item->cidadeid = $rota->item->cidadeid;
                    $item->rota = $rota->item->rota;
                    $item->ordem = $rota->item->ordem;
                    $ins = $item->save();
                    if (!$ins) throw new Exception("Rota não foi inserida - " . $rota->item-->rota);
                }
            }
        }
        // roteiro

        // despesas
        if (isset($request->despesas)) {
            foreach ($request->despesas as $despesa) {
                $despesa  =(object)$despesa;
                $despesa->item  =(object)$despesa->item;
                if ($despesa->action == 'delete') {
                    $del = AcertoViagemDespesas::find($despesa->item->id)->delete();
                    if (!$del) throw new Exception("Despesa não foi excluída - " . $despesa->item->despesaviagem->descricao);
                }
                if ($despesa->action == 'update') {
                    $item = AcertoViagemDespesas::find($despesa->item->id);
                    if ($item) {
                        $item->despesaviagemid = $despesa->item->despesaid;
                        $item->valor = $despesa->item->valor;
                        $ins = $item->save();
                        if (!$ins) throw new Exception("Despesa não foi atualizado - " . $despesa->item->despesaviagem->descricao);
                    }
                }
                if ($despesa->action == 'insert') {
                    $item = new AcertoViagemDespesas();
                    $item->acertoid = $dataset->id;
                    $item->despesaviagemid = $despesa->item->despesaid;
                    $item->valor = $despesa->item->valor;
                    $ins = $item->save();
                    if (!$ins) throw new Exception("Despesa não foi inserida - " . $despesa->item->despesaviagem->descricao);
                }
            }
        }
        // despesas

        // periodos
        if (isset($request->periodos)) {
            foreach ($request->periodos as $periodo) {
                $periodo  =(object)$periodo;
                $periodo->item  =(object)$periodo->item;
                if ($periodo->action == 'delete') {
                    $del = AcertoViagemPeriodo::find($periodo->item->id)->delete();
                    if (!$del) throw new Exception("Período não foi excluído");
                }
                if ($periodo->action == 'update') {
                    $item = AcertoViagemPeriodo::find($periodo->item->id);
                    if ($item) {
                        $item->dhi = $periodo->item->dhi;
                        $item->dhf = $periodo->item->dhf;
                        $item->dhf = $periodo->item->dhf;
                        $item->ordem = $periodo->item->ordem;
                        $item->obs = $periodo->item->obs;
                        $item->calculardiarias($param_limites);
                        $ins = $item->save();
                        if (!$ins) throw new Exception("Período não foi atualizado");
                    }
                }
                if ($periodo->action == 'insert') {
                    $item = new AcertoViagemPeriodo();
                    $item->acertoid = $dataset->id;
                    $item->dhi = $periodo->item->dhi;
                    $item->dhf = $periodo->item->dhf;
                    $item->ordem = $periodo->item->ordem;
                    $item->obs = $periodo->item->obs;
                    $item->calculardiarias($param_limites);
                    $ins = $item->save();
                    if (!$ins) throw new Exception("Período não foi inserido");
                }
            }
        }

        if (($action == 'update') && ($forceupdatevalorcalculo)) {
            $periodosrecal = AcertoViagemPeriodo::where('acertoid', '=', $dataset->id)->get();
            foreach ($periodosrecal as $periodo) {
                $periodo->calculardiarias($param_limites);
                $ins = $periodo->save();
                if (!$ins) throw new Exception("Período não foi recalculado");
            }
        }
        // periodos


        // abastecimentos
        if (isset($request->abastecimentos)) {
            foreach ($request->abastecimentos as $abastecimento) {
                $abastecimento  =(object)$abastecimento;
                $abastecimento->item  =(object)$abastecimento->item;
                if ($abastecimento->action == 'delete') {
                    $del = AcertoViagemAbastec::find($abastecimento->item->id)->delete();
                    if (!$del) throw new Exception("Abastecimento não foi excluído");
                }
                if ($abastecimento->action == 'update') {
                    $item = AcertoViagemAbastec::find($abastecimento->item->id);
                    if ($item) {
                        $item->data = $abastecimento->item->data;
                        $item->kmini = $abastecimento->item->kmini;
                        $item->kmfim = $abastecimento->item->kmfim;
                        $item->litros = $abastecimento->item->litros;
                        $item->vlrabastecimento = $abastecimento->item->vlrabastecimento;
                        $item->tipopagto = $abastecimento->item->tipopagto;
                        $ins = $item->save();
                        if (!$ins) throw new Exception("Abastecimento não foi atualizado");
                    }
                }
                if ($abastecimento->action == 'insert') {
                    $item = new AcertoViagemAbastec();
                    $item->acertoid = $dataset->id;
                    $item->data = $abastecimento->item->data;
                    $item->kmini = $abastecimento->item->kmini;
                    $item->kmfim = $abastecimento->item->kmfim;
                    $item->litros = $abastecimento->item->litros;
                    $item->vlrabastecimento = $abastecimento->item->vlrabastecimento;
                    $item->tipopagto = $abastecimento->item->tipopagto;
                    $ins = $item->save();
                    if (!$ins) throw new Exception("Abastecimento não foi inserido");
                }
            }
        }
        // abastecimentos

        // salvar totalizacao
        $total = AcertoViagemDespesas::where('acertoid', '=', $dataset->id)->sum('valor');
        if (!$total) $total = 0;
        $dataset->vlrtotaldespesas = round($total, 2);

        $total = AcertoViagemAbastec::where('acertoid', '=', $dataset->id)->sum('litros');
        if (!$total) $total = 0;
        $dataset->totallitros = round($total, 2);

        $total = AcertoViagemAbastec::where('acertoid', '=', $dataset->id)->sum('vlrabastecimento');
        if (!$total) $total = 0;
        $dataset->vlrtotalabastecimento = round($total, 2);

        $total = AcertoViagemAbastec::where('acertoid', '=', $dataset->id)->where('tipopagto', '=', 'D')->sum('vlrabastecimento');
        if (!$total) $total = 0;
        $dataset->vlrtotalabastecimentodinheiro = round($total, 2);

        //periodos
        $summary = AcertoViagemPeriodo::select(DB::raw('SUM(cafeqtde) as cafeqtde, SUM(almocoqtde) as almocoqtde, SUM(jantarqtde) as jantarqtde, SUM(pernoiteqtde) as pernoiteqtde'))
                                    ->where('acertoid', '=', $dataset->id)->get();

        $dataset->cafeextra = isset($request->cafeextra) ? $request->cafeextra : 0;
        $dataset->almocoextra = isset($request->almocoextra) ? $request->almocoextra : 0;
        $dataset->jantarextra = isset($request->jantarextra) ? $request->jantarextra : 0;
        $dataset->pernoiteextra = isset($request->pernoiteextra) ? $request->pernoiteextra : 0;

        $dataset->cafeqtde = 0;
        $dataset->almocoqtde = 0;
        $dataset->jantarqtde = 0;
        $dataset->pernoiteqtde = 0;
        if ($summary){
            if (count($summary) > 0) {
                $dataset->cafeqtde = intVal($summary[0]['cafeqtde']);
                $dataset->almocoqtde = intVal($summary[0]['almocoqtde']);
                $dataset->jantarqtde = intVal($summary[0]['jantarqtde']);
                $dataset->pernoiteqtde = intVal($summary[0]['pernoiteqtde']);
            }
        }
        $dataset->vlrtotaldiaria = round($dataset->cafeapagar + $dataset->almocoapagar + $dataset->jantarapagar + $dataset->pernoiteapagar, 2);


        $dataset->vlrsaldofinal = $dataset->vlradiantamentototal - ($dataset->vlrtotalabastecimentodinheiro + $dataset->vlrtotaldespesas + $dataset->vlrtotaldiaria);
        $dataset->save();

        DB::commit();

        $ret->id = $dataset->id;
        $ret->data = $dataset->export(true);
        $ret->msg = $action;
        $ret->ok = true;

      } catch (\Throwable $th) {
        DB::rollBack();
        $ret->msg = $th->getMessage();
      }


      return $ret->toJson();
    }

    public function encerrardesfazer(Request $request, $id)
    {
      $ret = new RetApiController;
      try {

        $usuario = session('usuario');
        if (!$usuario) throw new Exception('Nenhum usuário autenticado');

        $dataset = AcertoViagem::find($id);
        if (!$dataset) throw new Exception("Nenhum acerto encontrado");
        if ($dataset->status === 0) throw new Exception("Acerto de viagem está aberto");

      } catch (\Throwable $th) {
        $ret->msg = $th->getMessage();
        return $ret->toJson();
      }

      try {
        DB::beginTransaction();


        $dataset->status = 0;
        $dataset->dhacerto = null;
        $dataset->updated_usuarioid = $usuario->id;
        $dataset->save();

        DB::commit();

        $ret->id = $dataset->id;
        $ret->data = $dataset->export(true);
        $ret->ok = true;

      } catch (\Throwable $th) {
        DB::rollBack();
        $ret->msg = $th->getMessage();
      }


      return $ret->toJson();
    }

    public function encerrar(Request $request, $id)
    {
      $ret = new RetApiController;
      try {

        $usuario = session('usuario');
        if (!$usuario) throw new Exception('Nenhum usuário autenticado');

        $dataset = AcertoViagem::find($id);
        if (!$dataset) throw new Exception("Nenhum acerto encontrado");
        if ($dataset->status == 1) throw new Exception("Acerto de viagem foi encerrado");

        if (!($dataset->kmtotal >= 0)) throw new Exception("KM total não pode ser negativo. Ajuste o KM inicial e final.");



      } catch (\Throwable $th) {
        $ret->msg = $th->getMessage();
        return $ret->toJson();
      }

      try {
        DB::beginTransaction();


        $dataset->status = 1;
        $dataset->dhacerto = Carbon::now();
        $dataset->updated_usuarioid = $usuario->id;
        $dataset->save();

        DB::commit();

        $ret->id = $dataset->id;
        $ret->data = $dataset->export(true);
        $ret->ok = true;

      } catch (\Throwable $th) {
        DB::rollBack();
        $ret->msg = $th->getMessage();
      }


      return $ret->toJson();
    }

    public function printFichaLiberacao (Request $request)
    {
        $ret = new RetApiController;
        try {
            $disk = Storage::disk('public');

            $usuario = session('usuario');

            $ids = isset($request->ids) ? $request->ids : null;
            $ids = explode(",", $ids);
            if (!is_array($ids)) $ids[] = $ids;
            $ids = count($ids) > 0 ? $ids : null;
            if (!$ids) throw new Exception('Nenhum número de coleta informado');

            $rows = AcertoViagem::whereIn('id', $ids)->get();
            if (!$rows) throw new Exception('Nenhum registro encontrado');
            if ($rows->isEmpty()) throw new Exception('Nenhum registro encontrado com os dados fornecidos');

            $path = $disk->path('temp');
            if (!$disk->exists('temp')) $disk->makeDirectory('temp');

            $checklist = \App\auxiliares\Helper::getConfig('acerto_checklist', []);
            $quadroperiodosqtdelinha = \App\auxiliares\Helper::getConfig('acerto_quadro_periodos_qtdelinha', 1);
            $quadroabastqtdelinha = \App\auxiliares\Helper::getConfig('acerto_quadro_abast_qtdelinha', 1);
            $quadromanutencaoqtdelinha = \App\auxiliares\Helper::getConfig('acerto_quadro_manutencao_qtdelinha', 1);

            $html = view('pdf.acertoviagem.fichaliberacao', compact('rows', 'checklist', 'usuario', 'quadroabastqtdelinha', 'quadroperiodosqtdelinha', 'quadromanutencaoqtdelinha'))->render();
            $pdf = PDF::loadHtml($html);
            $filename = 'acerto-ficha-' . md5($html) . '.pdf';

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

    public function printAcertoDetalhe (Request $request)
    {
        $ret = new RetApiController;
        try {
            $disk = Storage::disk('public');

            $usuario = session('usuario');

            $ids = isset($request->ids) ? $request->ids : null;
            $ids = explode(",", $ids);
            if (!is_array($ids)) $ids[] = $ids;
            $ids = count($ids) > 0 ? $ids : null;
            if (!$ids) throw new Exception('Nenhum número de coleta informado');

            $rows = AcertoViagem::whereIn('id', $ids)->get();
            if (!$rows) throw new Exception('Nenhum registro encontrado');
            if ($rows->isEmpty()) throw new Exception('Nenhum registro encontrado com os dados fornecidos');

            $acertoinforelviagem = \App\auxiliares\Helper::getConfig('acerto_info_relviagem', '');

            $path = $disk->path('temp');
            if (!$disk->exists('temp')) $disk->makeDirectory('temp');

            $html = view('pdf.acertoviagem.acertodetalhe', compact('rows', 'usuario', 'acertoinforelviagem'))->render();
            $pdf = PDF::loadHtml($html);
            $filename = 'acertos-detalhe' . md5($html) . '.pdf';

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
