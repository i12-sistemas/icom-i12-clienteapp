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
use App\Http\Traits\EtiquetasTrait;

use App\Enums\CargaEntradaStatusEnumType;
use App\Models\CargaEntrada;
use App\Models\CargaEntradaItem;
use App\Models\ColetasNota;
use App\Models\Etiquetas;
use App\Models\Coletas;

class CargaEntradaController extends Controller
{

    use EtiquetasTrait;

    public function list(Request $request)
    {
      $ret = new RetApiController;
      try {
        $sortby = isset($request->sortby) ? $request->sortby : 'cargaentrada.dhentrada';
        $descending = isset($request->descending) ? $request->descending : 'asc';
        $created_at = isset($request->created_at) ? $request->created_at : null;
        $dhentradai = isset($request->dhentradai) ? $request->dhentradai : null;
        $dhentradaf = isset($request->dhentradaf) ? $request->dhentradaf : null;

        $status = isset($request->status) ? $request->status : null;
        $tipo = isset($request->tipo) ? $request->tipo : null;
        $erros = isset($request->erros) ? $request->erros : null;

        $pesoi = isset($request->pesoi) ? floatval($request->pesoi) : null;
        $pesof = isset($request->pesof) ? floatval($request->pesof) : null;

        $volqtdei = isset($request->volqtdei) ? floatval($request->volqtdei) : null;
        $volqtdef = isset($request->volqtdef) ? floatval($request->volqtdef) : null;
        $motoristastr = isset($request->motoristastr) ? utf8_decode($request->motoristastr) : null;
        $unidadeentradastr = isset($request->unidadeentradastr) ? utf8_decode($request->unidadeentradastr) : null;
        $veiculostr = isset($request->veiculostr) ? utf8_decode($request->veiculostr) : null;

        $find = isset($request->find) ? utf8_decode($request->find) : null;
        $unidadeentradaid = isset($request->unidadeentradaid) ? intVal($request->unidadeentradaid) : null;

        $orderby = null;
        if (isset($request->orderby)) {
            $orderby = json_decode($request->orderby,true);
            $orderbynew = [];
            foreach ($orderby as $key => $value) {
                if ($key == 'erros') {
                    $lKey = 'trim(cargaentrada.erroqtde)';
                } else if ($key == 'veiculostr') {
                    $lKey = 'trim(veiculo.placa)';
                } else if ($key == 'motoristastr') {
                    $lKey = 'trim(motorista.nome)';
                } else if ($key == 'regiao') {
                    $lKey = 'cidadecoleta.regiaoid';
                } else if ($key == 'enderecocoleta') {
                    $lKey = 'concat(cidadecoleta.cidade,cidadecoleta.uf)';
                } else if ($key == 'cidadedestino') {
                    $lKey = 'concat(cidadedestino.cidade,cidadedestino.uf)';
                } else {
                    $lKey = 'cargaentrada.' . $key;

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
        $query = CargaEntrada::select(DB::raw('cargaentrada.*'))
                    ->leftJoin('unidade', 'cargaentrada.unidadeentradaid', '=', 'unidade.id')
                    ->leftJoin('veiculo', 'cargaentrada.veiculoid', '=', 'veiculo.id')
                    ->leftJoin('motorista', 'cargaentrada.motoristaid', '=', 'motorista.id')
                    ->with( 'unidadeentrada', 'motorista', 'veiculo'  )
                    ->when(isset($request->find) && ($find !== ''), function ($query) use ($find) {
                      return $query->where(function($query2) use ($find) {
                        return $query2->where('cargaentrada.tipo', 'like', $find.'%')

                          ->orWhere('veiculo.placa', 'like', '%'.cleanDocMask($find).'%')

                          ->orWhere('motorista.nome', 'like', '%'.$find.'%')
                          ->orWhere('motorista.apelido', 'like','%'. $find.'%')

                          ->orWhere('unidade.razaosocial', 'like', '%'.$find.'%')
                          ->orWhere('unidade.fantasia', 'like','%'. $find.'%')
                          ;
                      });
                    })
                    ->when(isset($request->unidadeentradastr) && ($unidadeentradastr ? $unidadeentradastr !== '' : false), function ($query) use ($unidadeentradastr)  {
                        return $query->where(function($query2) use ($unidadeentradastr) {
                            return $query2->where('unidade.razaosocial', 'like', '%'.$unidadeentradastr.'%')
                                ->orWhere('unidade.fantasia', 'like', '%'.$unidadeentradastr.'%');
                        });
                    })
                    ->when(isset($request->motoristastr) && ($motoristastr ? $motoristastr !== '' : false), function ($query) use ($motoristastr)  {
                        return $query->where(function($query2) use ($motoristastr) {
                            return $query2->where('motorista.nome', 'like', '%'.$motoristastr.'%')
                                ->orWhere('motorista.apelido', 'like', '%'.$motoristastr.'%');
                        });
                    })
                    ->when(isset($request->veiculostr) && ($veiculostr ? $veiculostr !== '' : false), function ($query) use ($veiculostr)  {
                        return $query->where('veiculo.placa', 'like', '%'.cleanDocMask($veiculostr).'%');
                    })

                    ->when(isset($request->unidadeentradaid) && ($unidadeentradaid > 0), function ($query) use ($unidadeentradaid)  {
                        return $query->Where('cargaentrada.unidadeentradaid', '=', $unidadeentradaid);
                    })
                    ->when(isset($request->status) && ($status !== ''), function ($query) use ($status)  {
                        return $query->Where('cargaentrada.status', $status);
                    })
                    ->when(isset($request->tipo) && ($tipo !== ''), function ($query) use ($tipo)  {
                        return $query->Where('cargaentrada.tipo', $tipo);
                    })
                    ->when(isset($request->erros) && ($erros !== ''), function ($query) use ($erros)  {
                        if ($erros === '1') {
                            return $query->Where('cargaentrada.erroqtde', '>', 0);
                        } else {
                            return $query->Where('cargaentrada.erroqtde', '=', 0);
                        }
                    })
                    ->when(isset($request->pesoi), function ($query) use ($pesoi) {
                        return $query->Where('cargaentrada.peso', '>=', $pesoi);
                    })
                    ->when(isset($request->pesof), function ($query) use ($pesof) {
                        return $query->Where('cargaentrada.peso', '<=', $pesof);
                    })
                    ->when(isset($request->volqtdei), function ($query) use ($volqtdei) {
                        return $query->Where('cargaentrada.volqtde', '>=', $volqtdei);
                    })
                    ->when(isset($request->volqtdef), function ($query) use ($volqtdef) {
                        return $query->Where('cargaentrada.volqtde', '<=', $volqtdef);
                    })

                    ->when(isset($request->created_at), function ($query) use ($created_at) {
                        return $query->Where(DB::Raw('date(cargaentrada.created_at)'), '=', $created_at);
                    })

                    ->when(isset($request->dhentradai), function ($query) use ($dhentradai) {
                        return $query->Where(DB::Raw('date(cargaentrada.dhentrada)'), '>=', $dhentradai);
                    })
                    ->when(isset($request->dhentradaf), function ($query) use ($dhentradaf) {
                        return $query->Where(DB::Raw('date(cargaentrada.dhentrada)'), '<=', $dhentradaf);
                    })
                    ->when($request->orderby, function ($query) use ($orderby) {
                        foreach ($orderby as $key => $value) {
                            $query->orderByRaw($key  . ' ' . $value);
                        }
                        return $query;
                    });

        $dataset = $query->paginate($perpage);
        $totalerros = $query->sum('erroqtde');
        $counters = [
            'totalerros' => $totalerros
        ];

        $dados = [];
        foreach ($dataset as $row) {
            $dados[] = $row->export(false);
        }
        $ret->counters = $counters;
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

        $dataset = CargaEntrada::find($find);
        if (!$dataset) throw new Exception("Carga de entrada não foi encontrada");

        $ret->data = $dataset->export(true);
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

        $id = isset($request->id) ? intVal($request->id) : 0;
        $action =  $id > 0 ? 'update' : 'add';


        if ($action=='update') {
            $carga = CargaEntrada::find($id);
            if (!$carga) throw new Exception("Carga de entrada não foi encontrada");
        }

      } catch (\Throwable $th) {
        $ret->msg = $th->getMessage();
        return $ret->toJson();
      }

      try {
        DB::beginTransaction();

        if ($action=='add') {
          $carga = new CargaEntrada();
          $carga->tipo = $request->tipo;
          $carga->status = '1';
          $carga->dhentrada = $request->dhentrada;
          $carga->useridcreated = $usuario->id;
          $carga->volqtde = 0;
          $carga->peso = 0;
          $carga->erroqtde = 0;
          $carga->editadomanualmente = 0;
        }
        $carga->unidadeentradaid = $request->unidadeentradaid;
        $carga->motoristaid = $request->motoristaid;
        $carga->veiculoid = $request->veiculoid;
        $carga->useridupdated = $usuario->id;

        $carga->totaliza();
        $upd = $carga->save();

        DB::commit();

        $ret->id = $carga->id;
        $ret->data = $carga->export(true);
        $ret->ok = true;
      } catch (\Throwable $th) {
        DB::rollBack();
        $ret->msg = $th->getMessage();
      }


      return $ret->toJson();
    }

    public function delete(Request $request, $id)
    {
      $ret = new RetApiController;
      try {

        $usuario = session('usuario');
        if (!$usuario) throw new Exception('Nenhum usuário autenticado');

        $id = isset($request->id) ? intVal($request->id) : 0;
        $action =  $id > 0 ? 'update' : 'add';

        $carga = CargaEntrada::find($id);
        if (!$carga) throw new Exception("Carga de entrada não foi encontrada");

        if ($carga->status !== '1' ) throw new Exception("Status atual não permite exclusão.");
        // if ($carga->itens->count() > 0 ) throw new Exception("Carga de entrada contêm itens e não pode ser excluida não foi encontrada.");

      } catch (\Throwable $th) {
        $ret->msg = $th->getMessage();
        return $ret->toJson();
      }

      try {
        DB::beginTransaction();

        foreach ($carga->itens as $cargaItem) {
          $qtdeEtiquetas = $cargaItem->etiquetas->count();
          if ($qtdeEtiquetas > 0)
              Etiquetas::where('cargaentradaitem', '=', $cargaItem->id)->get()->each(function($eti) use ($usuario) {
                  $eti->useridupdated = $usuario->id;
                  $eti->delete();
              });

          $cargaItem->delete();
        }


        $carga->delete();

        DB::commit();

        $ret->ok = true;
      } catch (\Throwable $th) {
        DB::rollBack();
        $ret->msg = $th->getMessage();
      }


      return $ret->toJson();
    }

    public function changestatus($id, $status)
    {
      $ret = new RetApiController;
      try {

        $usuario = session('usuario');
        if (!$usuario) throw new Exception('Nenhum usuário autenticado');

        if (($status !== '1') && ($status !== '2')) throw new Exception("Status inválido");

        $carga = CargaEntrada::find($id);
        if (!$carga) throw new Exception("Carga de entrada não foi encontrada");

        if ($status === '2') {
            if ($carga->status == '2') throw new Exception("Carga de entrada já foi encerrada");
            if ($carga->erroqtde > 0) throw new Exception("Não é possível encerrar a carga de entrada pois existem erros");
            if (!$carga->itens) throw new Exception("Carga sem itens");
            if ($carga->itens->count() === 0) throw new Exception("Carga sem itens");
            if ($carga->conferidoprogresso < 100) throw new Exception("Carga não foi totalmente conferida");
        }

        if ($status === '1') {
            if ($carga->status == '1') throw new Exception("Carga de entrada está aberta atualmente");
        }

      } catch (\Throwable $th) {
        $ret->msg = $th->getMessage();
        return $ret->toJson();
      }

      try {
        DB::beginTransaction();

        $statusDe = $carga->status;

        $carga->status = $status;
        $carga->useridupdated = $usuario->id;
        $upd = $carga->save();

        foreach ($carga->itens as $item) {
            foreach ($item->etiquetas as $etiqueta) {

                $etiqueta->travado = ($carga->status == CargaEntradaStatusEnumType::tceEncerrado) ? 0 : 1;
                $etiqueta->useridupdated = $usuario->id;
                $etiqueta->save();

                $log = $this->addLog($etiqueta, $usuario->id, 'cargaentradaitem', $etiqueta->cargaentradaitem, 'update',
                            'Alteração de status da carda de ' . $statusDe . '-' . CargaEntradaStatusEnumType::getDescription($statusDe) .
                            ' para ' . $carga->status . '-' . CargaEntradaStatusEnumType::getDescription($carga->status)
                        );
                if (!$log) throw new Exception("Nenhum log da etiqueta foi gerado ao criar etiqueta");
            }
        }

        DB::commit();

        $ret->id = $carga->id;
        $ret->data = $carga->export(true);
        $ret->ok = true;
      } catch (\Throwable $th) {
        DB::rollBack();
        $ret->msg = $th->getMessage();
      }


      return $ret->toJson();
    }

    public function printDetalhe (Request $request, $id)
    {
        $ret = new RetApiController;
        try {
            $disk = Storage::disk('public');

            $usuario = session('usuario');

            $carga = CargaEntrada::find($id);
            if (!$carga) throw new Exception("Carga de entrada não foi encontrada");

            // $acertoinforelviagem = \App\auxiliares\Helper::getConfig('acerto_info_relviagem', '');

            $path = $disk->path('temp');
            if (!$disk->exists('temp')) $disk->makeDirectory('temp');

            $html = view('pdf.cargaentrada.fichadetalhada', compact('carga', 'usuario'))->render();
            $pdf = PDF::loadHtml($html);
            $filename = 'cargaentrada' . md5($html) . '.pdf';

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

    public function item_conferir(Request $request, $cargaid)
    {
      $ret = new RetApiController;
      try {
        $ean13 = isset($request->ean13) ? $request->ean13 : null;
        if (!$ean13) throw new Exception("Nenhum código de barra informado");

        $usuario = session('usuario');
        if (!$usuario) throw new Exception('Nenhum usuário autenticado');

        $dispositivo = session('dispositivo');

        $carga = CargaEntrada::find($cargaid);
        if (!$carga) throw new Exception("Carga de entrada não foi encontrada");

        $etiqueta = Etiquetas::where('ean13', '=', $ean13)->first();
        if (!$etiqueta) throw new Exception("Nenhum volume encontrado com o código");
        if ($etiqueta->itemcargaentrada->cargaentradaid !== $carga->id) throw new Exception("Este volume não pertence a carga informada");
        if ($etiqueta->itemcargaentrada->errors !== '') throw new Exception("O item da carga de entrada contêm erros");
        if ($etiqueta->conferidoentrada === 1) throw new Exception("Volume já foi conferido anteriormente");

      } catch (\Throwable $th) {
        $ret->msg = $th->getMessage();
        return $ret->toJson();
      }

      try {
        DB::beginTransaction();

        $etiqueta->conferidoentrada = 1;
        $etiqueta->conferidoentradauserid = $usuario->id;
        $etiqueta->conferidoentradadh = Carbon::now();
        $etiqueta->useridupdated = $usuario->id;
        $etiqueta->conferidoentradauuid = $dispositivo ? $dispositivo->uuid : null;

        $upd = $etiqueta->save();

        $carga->totaliza();
        $carga->useridupdated = $usuario->id;
        $carga->updated_at = Carbon::now();
        $upd = $carga->save();

        DB::commit();

        try {
            if (($carga->status == '1') && ($carga->erroqtde == 0) && ($carga->conferidoprogresso == 100)) {
                $retorno = $this->changestatus($carga->id, '2');
                $retorno = (object)$retorno->getOriginalContent();
                if ($retorno->ok) {
                    $carga = CargaEntrada::find($cargaid);
                }
            }
        } catch (\Throwable $th) {
            \Log::error($th->getMessage());
        }
        $ret->id = $carga->id;
        $ret->data = $carga->export(true);
        $ret->ok = true;
      } catch (\Throwable $th) {
        DB::rollBack();
        $ret->msg = $th->getMessage();
      }


      return $ret->toJson();
    }


    public function item_save(Request $request, $cargaid)
    {
      $ret = new RetApiController;
      try {

        $usuario = session('usuario');
        if (!$usuario) throw new Exception('Nenhum usuário autenticado');

        $id = isset($request->id) ? intVal($request->id) : 0;
        $action =  $id > 0 ? 'update' : 'add';
        $coletaid = null;

        $carga = CargaEntrada::find($cargaid);
        if (!$carga) throw new Exception("Carga de entrada não foi encontrada");

        if ($action=='update') {
            $cargaItem = CargaEntradaItem::find($id);
            if (!$cargaItem) throw new Exception("Item da carga não foi encontrado");
            if (!$request->has('nfevol') && !$request->has('nfepeso'))
                throw new Exception("Informe o peso ou volume para ajustar");

            if (intVal($request->nfevol) > 999) throw new Exception("Volume máximo é de 999 itens");
            if (intVal($request->nfevol) < 0) throw new Exception("Volume mínimo é zero");

            if ($request->has('coletaid')) {
                if ((!$cargaItem->coletaid) || ($cargaItem->coletaid !== intval($request->coletaid))) {
                    $coletaid = intval($request->coletaid);
                    $coleta = Coletas::find($coletaid);
                    if (!$coleta) throw new Exception("Nenhuma coleta encontrada com o número " . $coletaid);
                    $coletaid = $coleta->id;
                }
            }
        } else {
            $nfechave = isset($request->nfechave) ? $request->nfechave : '';
            if ($nfechave == '') throw new Exception("Obrigatório informar a chave da NF-e");

            if (!testaChaveNFe($nfechave)) throw new Exception("Chave da NF-e inválida!");

            $nfecheck = CargaEntradaItem::where('nfechave', '=', $nfechave)->whereRaw('if(?>0, not(id=?), true)', [$id, $id])->first();
            if ($nfecheck) throw new Exception("A chave informada já foi lançada na carga de entrada # " . $nfecheck->cargaentrada->id .
                        ' do dia ' . $nfecheck->cargaentrada->dhentrada->format('d/m/Y') .
                        ' na unidade ' . $nfecheck->cargaentrada->unidadeentrada->fantasia);

            $chavedecode = decodeChaveNFe($nfechave);
            $coletanota = ColetasNota::where('notachave','=', $nfechave)->orderBy('dhlocal_data', 'DESC')->first();
            if (!$coletanota) throw new Exception("Nota fiscal não passou pela guarita");
            if (!($coletanota->idcoleta))
                throw new Exception("Nota fiscal sem coleta gerada - Motivo: " . $coletanota->ultimoerro);
        }

      } catch (\Throwable $th) {
        $ret->msg = $th->getMessage();
        return $ret->toJson();
      }

      try {
        DB::beginTransaction();

        if ($action=='add') {
            $cargaItem = new CargaEntradaItem();
            $cargaItem->cargaentradaid = $carga->id;
            $cargaItem->nfechave = $nfechave;
            $cargaItem->useridcreated = $usuario->id;
            if ($chavedecode) {
                $cargaItem->nfenumero = $chavedecode["nNF"];
                $cargaItem->nfecnpj =  $chavedecode["CNPJ"];
            }
            $cargaItem->nfevol = $request->nfevol;
            $cargaItem->nfepeso = $request->nfepeso;
            $cargaItem->coletanotaid = $coletanota->id;
            $cargaItem->nfevol = $coletanota->qtde;
            $cargaItem->nfepeso = $coletanota->peso;
            if ($coletanota->idcoleta > 0) $cargaItem->coletaid = $coletanota->idcoleta;
            $cargaItem->tipoprocessamento = '1'; //auto
        } else {
            $cargaItem->tipoprocessamento = '2'; //manual
            $cargaItem->manualuserid = $usuario->id;

            if ($request->has('nfevol')) $cargaItem->nfevol = $request->nfevol;
            if ($request->has('nfepeso')) $cargaItem->nfepeso = $request->nfepeso;
            if ($coletaid ? $coletaid > 0 : false) $cargaItem->coletaid = $coletaid;

            $qtdeEtiquetas = $cargaItem->etiquetas->count();
            if ($qtdeEtiquetas > 0) {
                if ($qtdeEtiquetas !== $cargaItem->nfevol) {
                    Etiquetas::where('cargaentradaitem', '=', $cargaItem->id)->get()->each(function($eti) use ($usuario) {
                        $eti->useridupdated = $usuario->id;
                        $eti->delete();
                    });
                    $cargaItem->load('etiquetas');
                }
            }
        }
        $cargaItem->useridupdated = $usuario->id;
        $cargaItem->checkErros();
        $upd = $cargaItem->save();


        // gerar etiquetas
        if ($action=='add') {
            if (($cargaItem->coletaid > 0) && ($cargaItem->nfevol > 0)) {
                $cc = app()->make('App\Http\Controllers\api\v1\EtiquetasController');
                $params = [
                    'cargaentradaitem' => $cargaItem->id,
                    'unidadeatualid' => $carga->unidadeentradaid,
                    'usuarioid' => $usuario->id,
                    'volumeinicial' => 1,
                    'volumetotal' => $cargaItem->nfevol,
                    'pesototal' => $cargaItem->nfepeso,
                ];
                $retProc = app()->call([$cc, 'etiquetas_add_lote'], $params);
                if ($retProc->ok) {
                    $cargaItem = CargaEntradaItem::find($cargaItem->id);
                    $cargaItem->checkErros();
                    $cargaItem->save();
                }
            }
        }

        $carga->totaliza();
        $carga->useridupdated = $usuario->id;
        $carga->updated_at = Carbon::now();
        $upd = $carga->save();

        DB::commit();

        $ret->id = $cargaItem->id;
        $ret->data = $cargaItem->export(true);
        $ret->ok = true;
      } catch (\Throwable $th) {
        DB::rollBack();
        $ret->msg = $th->getMessage();
      }


      return $ret->toJson();
    }

    public function item_delete(Request $request, $cargaid, $itemid)
    {
      $ret = new RetApiController;
      try {

        $usuario = session('usuario');
        if (!$usuario) throw new Exception('Nenhum usuário autenticado');

        $carga = CargaEntrada::find($cargaid);
        if (!$carga) throw new Exception("Carga de entrada não foi encontrada");

        $cargaItem = CargaEntradaItem::where('id', '=', $itemid)->where('cargaentradaid', '=', $cargaid)->first();
        if (!$cargaItem) throw new Exception("Item da carga não foi encontrado");

      } catch (\Throwable $th) {
        $ret->msg = $th->getMessage();
        return $ret->toJson();
      }

      try {
        DB::beginTransaction();

        $qtdeEtiquetas = $cargaItem->etiquetas->count();
        if ($qtdeEtiquetas > 0)
            Etiquetas::where('cargaentradaitem', '=', $cargaItem->id)->get()->each(function($eti) use ($usuario) {
                $eti->useridupdated = $usuario->id;
                $eti->delete();
            });

        $cargaItem->delete();

        $carga->totaliza();
        $carga->useridupdated = $usuario->id;
        $carga->updated_at = Carbon::now();
        $upd = $carga->save();


        DB::commit();

        $ret->ok = true;
      } catch (\Throwable $th) {
        DB::rollBack();
        $ret->msg = $th->getMessage();
      }

      return $ret->toJson();
    }

    public function etiquetas_gerar(Request $request, $cargaid, $itemid)
    {
      $ret = new RetApiController;
      try {

        $usuario = session('usuario');
        if (!$usuario) throw new Exception('Nenhum usuário autenticado');

        $id = isset($request->id) ? intVal($request->id) : 0;
        $action =  $id > 0 ? 'update' : 'add';

        $carga = CargaEntrada::find($cargaid);
        if (!$carga) throw new Exception("Carga de entrada não foi encontrada");

        $cargaItem = CargaEntradaItem::where('cargaentradaid', '=', $carga->id)->where('id', '=', $itemid)->first();
        if (!$cargaItem) throw new Exception("Item da carga não foi encontrado");

        if (!($cargaItem->nfevol > 0)) throw new Exception("Carga sem volume informado");
        if (!($cargaItem->nfepeso > 0)) throw new Exception("Carga sem peso informado");


      } catch (\Throwable $th) {
        $ret->msg = $th->getMessage();
        return $ret->toJson();
      }

      try {

        $cc = app()->make('App\Http\Controllers\api\v1\EtiquetasController');

        $params = [
            'cargaentradaitem' => $cargaItem->id,
            'unidadeatualid' => $carga->unidadeentradaid,
            'usuarioid' => $usuario->id,
            'volumeinicial' => 1,
            'volumetotal' => $cargaItem->nfevol,
            'pesototal' => $cargaItem->nfepeso,
        ];
        $retProc = app()->call([$cc, 'etiquetas_add_lote'], $params);
        if (!$retProc->ok) throw new Exception($retProc->msg);

        DB::beginTransaction();
        $cargaItem->useridupdated = $usuario->id;
        $cargaItem->checkErros();
        $upd = $cargaItem->save();

        $carga->totaliza();
        $carga->useridupdated = $usuario->id;
        $carga->updated_at = Carbon::now();
        $upd = $carga->save();

        DB::commit();


        if ($retProc->data) $ret->data = $retProc->data;
        $ret->ok = true;
      } catch (\Throwable $th) {
        DB::rollBack();
        $ret->msg = $th->getMessage();
      }


      return $ret->toJson();
    }


}
