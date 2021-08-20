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

use App\Enums\CargaTransferStatusEnumType;
use App\Enums\EtiquetasStatusEnumType;
use App\Models\CargaTransfer;
use App\Models\CargaTransferItem;
use App\Models\Etiquetas;
use App\Models\Paletes;

use App\Http\Traits\EtiquetasTrait;

class CargaTransferController extends Controller
{
    use EtiquetasTrait;

    public function list(Request $request)
    {
      $ret = new RetApiController;
      try {
        $sortby = isset($request->sortby) ? $request->sortby : 'cargatransfer.dhentrada';
        $descending = isset($request->descending) ? $request->descending : 'asc';

        // em qualquer unidade
        $id = isset($request->id) ? intVal($request->id) : null;
        $unidadeid = isset($request->unidadeid) ? $request->unidadeid : null;
        $unidadeentradaid = isset($request->unidadeentradaid) ? $request->unidadeentradaid : null;
        $unidadesaidaid = isset($request->unidadesaidaid) ? $request->unidadesaidaid : null;
        $cidades = isset($request->status) ? $request->status : null;

        $created_ati = isset($request->created_ati) ? $request->created_ati : null;
        $created_atf = isset($request->created_atf) ? $request->created_atf : null;

        $saidadhi = isset($request->saidadhi) ? $request->saidadhi : null;
        $saidadhf = isset($request->saidadhf) ? $request->saidadhf : null;

        $entradadhi = isset($request->entradadhi) ? $request->entradadhi : null;
        $entradadhf = isset($request->entradadhf) ? $request->entradadhf : null;

        $unidadesaidastr = isset($request->unidadesaidastr) ? utf8_decode($request->unidadesaidastr) : null;
        $unidadeentradastr = isset($request->unidadeentradastr) ? utf8_decode($request->unidadeentradastr) : null;
        $motoristastr = isset($request->motoristastr) ? utf8_decode($request->motoristastr) : null;
        $veiculostr = isset($request->veiculostr) ? utf8_decode($request->veiculostr) : null;
        $find = isset($request->find) ? utf8_decode($request->find) : null;
        $status = null;
        if (isset($request->status)) {
            try {
                $status = explode(",", $request->status);
            } catch (\Throwable $th) {
                $status = null;
            }
            if (!is_array($status)) $status[] = $status;
            $status = count($status) > 0 ? $status : null;
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
                    $lKey = 'cargatransfer.' . $key;

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
        // $numero = isset($request->numero) ? intVal($request->numero) : null;
        // if ($numero) {
        //     if (!($numero>0)) $numero = null;

        //     if ($numero>0) {
        //         $dhcoletaf = null;
        //         $dhcoletaf = null;
        //         $dhbaixai = null;
        //         $dhbaixaf = null;
        //         $situacao = null;
        //         $origem = null;
        //         $find  = null;
        //     }
        // } else {
        //     if ($find != '') {
        //         $n = intval($find);
        //         if ($n > 0) $numero = $n;
        //     }
        // }

        $perpage = isset($request->perpage) ? $request->perpage : 25;
        $query = CargaTransfer::select(DB::raw('cargatransfer.*'))
                    ->leftJoin('unidade as unidadeentrada', 'cargatransfer.unidadeentradaid', '=', 'unidadeentrada.id')
                    ->leftJoin('unidade as unidadesaida', 'cargatransfer.unidadesaidaid', '=', 'unidadesaida.id')
                    ->leftJoin('veiculo', 'cargatransfer.veiculoid', '=', 'veiculo.id')
                    ->leftJoin('motorista', 'cargatransfer.motoristaid', '=', 'motorista.id')
                    ->with( 'unidadeentrada', 'motorista', 'veiculo'  )
                    ->when(isset($request->find) && ($find !== ''), function ($query) use ($find) {
                        return $query->where(function($query2) use ($find) {
                          return $query2->where('cargatransfer.erromsg', 'like', $find.'%')

                            ->orWhere('veiculo.placa', 'like', '%'.cleanDocMask($find).'%')

                            ->orWhere('motorista.nome', 'like', '%'.$find.'%')
                            ->orWhere('motorista.apelido', 'like','%'. $find.'%')

                            ->orWhere('unidadeentrada.razaosocial', 'like', '%'.$find.'%')
                            ->orWhere('unidadeentrada.fantasia', 'like','%'. $find.'%')

                            ->orWhere('unidadesaida.razaosocial', 'like', '%'.$find.'%')
                            ->orWhere('unidadesaida.fantasia', 'like','%'. $find.'%')
                            ;
                        });
                      })
                    ->when(isset($request->id) && ($id > 0), function ($query) use ($id)  {
                        return $query->where('cargatransfer.id', '=', $id);
                    })
                    ->when(isset($request->unidadesaidaid) && ($unidadesaidaid > 0), function ($query) use ($unidadesaidaid)  {
                        return $query->where('cargatransfer.unidadesaidaid', '=', $unidadesaidaid);
                    })
                    ->when(isset($request->unidadeentradaid) && ($unidadeentradaid > 0), function ($query) use ($unidadeentradaid)  {
                        return $query->where('cargatransfer.unidadeentradaid', '=', $unidadeentradaid);
                    })
                    ->when(isset($request->unidadeid) && ($unidadeid > 0), function ($query) use ($unidadeid)  {
                        return $query->where(function($query2) use ($unidadeid) {
                            return $query2->where('cargatransfer.unidadeentradaid', '=', $unidadeid)
                                          ->orwhere('cargatransfer.unidadesaidaid', '=', $unidadeid);
                        });
                    })
                    ->when(isset($request->status) && (is_array($status)), function ($query) use ($status)  {
                        return $query->whereIn('cargatransfer.status', $status);
                    })


                    ->when(isset($request->unidadesaidastr) && ($unidadesaidastr ? $unidadesaidastr !== '' : false), function ($query) use ($unidadesaidastr)  {
                        return $query->where(function($query2) use ($unidadesaidastr) {
                            return $query2->where('unidadesaida.razaosocial', 'like', '%'.$unidadesaidastr.'%')
                                ->orWhere('unidadesaida.fantasia', 'like', '%'.$unidadesaidastr.'%');
                        });
                    })

                    ->when(isset($request->unidadeentradastr) && ($unidadeentradastr ? $unidadeentradastr !== '' : false), function ($query) use ($unidadeentradastr)  {
                        return $query->where(function($query2) use ($unidadeentradastr) {
                            return $query2->where('unidadeentrada.razaosocial', 'like', '%'.$unidadeentradastr.'%')
                                ->orWhere('unidadeentrada.fantasia', 'like', '%'.$unidadeentradastr.'%');
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

                    // ->when(isset($request->clientedestinostr) && ($clientedestinostr ? $clientedestinostr !== '' : false), function ($query) use ($clientedestinostr)  {
                    //     return $query->where(function($query2) use ($clientedestinostr) {
                    //         return $query2->where('clientedestino.razaosocial', 'like', '%'.$clientedestinostr.'%')
                    //         ->orWhere('clientedestino.fantasia', 'like', '%'.$clientedestinostr.'%');
                    //     });
                    // })
                    // ->when(isset($request->motoristastr) && ($motoristastr ? $motoristastr !== '' : false), function ($query) use ($motoristastr)  {
                    //     return $query->where(function($query2) use ($motoristastr) {
                    //         return $query2->where('motorista.nome', 'like', '%'.$motoristastr.'%')
                    //         ->orWhere('motorista.apelido', 'like', '%'.$motoristastr.'%');
                    //     });
                    // })

                    // ->when(isset($request->enderecocoletastr) && ($enderecocoletastr ? $enderecocoletastr !== '' : false), function ($query) use ($enderecocoletastr)  {
                    //     return $query->where(function($query2) use ($enderecocoletastr) {
                    //         return $query2->where('cidadecoleta.cidade', 'like', '%'.$enderecocoletastr.'%')
                    //         ->orWhere('cidadecoleta.uf', 'like', '%'.$enderecocoletastr.'%');
                    //     });
                    // })

                    // ->when(isset($request->cidadedestinostr) && ($cidadedestinostr ? $cidadedestinostr !== '' : false), function ($query) use ($cidadedestinostr)  {
                    //     return $query->where(function($query2) use ($cidadedestinostr) {
                    //         return $query2->where('cidadedestino.cidade', 'like', '%'.$cidadedestinostr.'%')
                    //         ->orWhere('cidadedestino.uf', 'like', '%'.$cidadedestinostr.'%');
                    //     });
                    // })


                    ->when(isset($request->created_ati), function ($query) use ($created_ati) {
                        return $query->Where(DB::Raw('date(cargatransfer.created_at)'), '>=', $created_ati);
                    })
                    ->when(isset($request->created_atf), function ($query) use ($created_atf) {
                        return $query->Where(DB::Raw('date(cargatransfer.created_at)'), '<=', $created_atf);
                    })


                    ->when(isset($request->saidadhi), function ($query) use ($saidadhi) {
                        return $query->Where(DB::Raw('date(cargatransfer.saidadh)'), '>=', $saidadhi);
                    })
                    ->when(isset($request->saidadhf), function ($query) use ($saidadhf) {
                        return $query->Where(DB::Raw('date(cargatransfer.saidadh)'), '<=', $saidadhf);
                    })


                    ->when(isset($request->entradadhi), function ($query) use ($entradadhi) {
                        return $query->Where(DB::Raw('date(cargatransfer.entradadh)'), '>=', $entradadhi);
                    })
                    ->when(isset($request->entradadhf), function ($query) use ($entradadhf) {
                        return $query->Where(DB::Raw('date(cargatransfer.entradadh)'), '<=', $entradadhf);
                    })



                    // ->when(isset($request->regiaostr) && ($regiaostr ? $regiaostr !== '' : false), function ($query) use ($regiaostr)  {
                    //     return $query->Where('cidadecoleta.regiaoid', intval($regiaostr));
                    // })
                    // ->when($numero, function ($query, $numero) {
                    //     return $query->Where('coletas.id', $numero);
                    // })
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
            $dados[] = $row->export();
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

        $dataset = CargaTransfer::find($find);
        if (!$dataset) throw new Exception("Carga de transferência não foi encontrada");

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

        $rules = [
            'motoristaid' => ['required', 'exists:motorista,id'],
            'veiculoid' => ['required', 'exists:veiculo,id'],
            'unidadeentradaid' => ['required', 'exists:unidade,id'],
            'unidadesaidaid' => ['required', 'exists:unidade,id'],
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
        $action =  $id > 0 ? 'update' : 'add';


        if ($action=='update') {
            $carga = CargaTransfer::find($id);
            if (!$carga) throw new Exception("Carga de transferência não foi encontrada");
            if ($carga->itens->count() > 0) throw new Exception("Carga de transferência bloqueada pois contêm itens");
        }



      } catch (\Throwable $th) {
        $ret->msg = $th->getMessage();
        return $ret->toJson();
      }

      try {
        DB::beginTransaction();

        if ($action=='add') {
            $carga = new CargaTransfer();
            $carga->status = '1';
            $carga->useridcreated = $usuario->id;
            $carga->volqtde = 0;
            $carga->peso = 0;
            $carga->erroqtde = 0;
        }
        $carga->unidadesaidaid = $request->unidadesaidaid;
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

        $carga = CargaTransfer::find($id);
        if (!$carga) throw new Exception("Carga de transferência não foi encontrada");
        if ($carga->status !== '1' ) throw new Exception("Status atual não permite exclusão.");

      } catch (\Throwable $th) {
        $ret->msg = $th->getMessage();
        return $ret->toJson();
      }

      try {
        DB::beginTransaction();

        $itens = $carga->itens;
        foreach ($itens as $item) {

            $etiqueta = $item->etiqueta;
            $etiqueta->travado = 0;
            $etiqueta->useridupdated = $usuario->id;
            $etiqueta->save();

            $item->delete();
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

        $carga = CargaTransfer::find($id);
        if (!$carga) throw new Exception("Carga de transferência não foi encontrada");

        if (($status !== '1') && ($status !== '2') && ($status !== '3') && ($status !== '4')) throw new Exception("Status inválido");

        // se diferente de 1=Em aberto
        if ($status !== CargaTransferStatusEnumType::tctEmAberto) {
            if (!$carga->itens) throw new Exception("Não é possível alterar o status de uma carga vazia");
            if (!($carga->itens->count() > 0)) throw new Exception("Não é possível alterar o status de uma carga vazia");

            if ($carga->erroqtde > 0) throw new Exception("Não é possível alterar o status de uma carga com erros");
        }

        if ($status === CargaTransferStatusEnumType::tctEncerrado) {
            if ($carga->status == CargaTransferStatusEnumType::tctEncerrado) throw new Exception("Carga já foi encerrada");
            if ($carga->erroqtde > 0) throw new Exception("Não é possível encerrar a carga pois existem erros");
            if (!$carga->itens) throw new Exception("Carga sem itens");
            if ($carga->itens->count() === 0) throw new Exception("Carga sem itens");
            if ($carga->conferidoentradaprogresso < 100) throw new Exception("Carga não foi totalmente conferida");
        }

        // if ($status === '1') {
        //     if ($carga->status == '1') throw new Exception("Carga de transferência está aberta atualmente");
        // }

      } catch (\Throwable $th) {
        $ret->msg = $th->getMessage();
        return $ret->toJson();
      }

      try {
        DB::beginTransaction();

        if (($status === CargaTransferStatusEnumType::tctEmAberto) || ($status === CargaTransferStatusEnumType::tctLiberadoCarregarTrans)) {
            $carga->saidauserid = null;
            $carga->saidadh = null;

            $carga->entradauserid = null;
            $carga->entradadh = null;
        }
        if ($status === CargaTransferStatusEnumType::tctEmTransito) {
            if (($carga->status === CargaTransferStatusEnumType::tctEmAberto) || ($carga->status === CargaTransferStatusEnumType::tctLiberadoCarregarTrans)) {
                $carga->saidauserid = $usuario->id;
                $carga->saidadh = Carbon::now();
            }

            $carga->entradauserid = null;
            $carga->entradadh = null;
        }

        if ($status === CargaTransferStatusEnumType::tctEncerrado) {
            $carga->entradauserid = $usuario->id;
            $carga->entradadh = Carbon::now();
        }

        foreach ($carga->itens as $item) {
            $etiqueta = $item->etiqueta;

            $etiqueta->travado = ($status == CargaTransferStatusEnumType::tctEncerrado) ? 0 : 1;
            if ($status == CargaTransferStatusEnumType::tctEncerrado) $etiqueta->unidadeatualid = $carga->unidadeentradaid;
            if ($status !== CargaTransferStatusEnumType::tctEncerrado) $etiqueta->unidadeatualid = $carga->unidadesaidaid;
            if ($status === CargaTransferStatusEnumType::tctEmTransito) {
                $etiqueta->status = EtiquetasStatusEnumType::EmTransferencia;
            } else {
                $etiqueta->status = EtiquetasStatusEnumType::EmDeposito;
            }
            $etiqueta->useridupdated = $usuario->id;
            $etiqueta->save();

            $log = $this->addLog($etiqueta, $usuario->id, 'cargatransferitem', $item->id, 'update',
                        'Alteração de status da carda de ' . $carga->status . '-' . CargaTransferStatusEnumType::getDescription($carga->status) .
                        ' para ' . $status . '-' . CargaTransferStatusEnumType::getDescription($status)
                    );
            if (!$log) throw new Exception("Nenhum log da etiqueta foi gerado ao criar etiqueta");
        }

        $carga->useridupdated = $usuario->id;
        $carga->status = $status;
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


    public function item_conferir(Request $request, $cargaid)
    {
      $start_time = microtime(TRUE);
      $ret = new RetApiController;
      try {
        $ean13 = isset($request->ean13) ? $request->ean13 : null;
        if (!$ean13) throw new Exception("Nenhum código de barra informado");

        $usuario = session('usuario');
        if (!$usuario) throw new Exception('Nenhum usuário autenticado');

        $dispositivo = session('dispositivo');

        $carga = CargaTransfer::find($cargaid);
        if (!$carga) throw new Exception("Carga de e não foi encontrada");
        if ($carga->status === CargaTransferStatusEnumType::tctEncerrado) throw new Exception("Carga já foi encerrada!");
        if ($carga->status !== CargaTransferStatusEnumType::tctEmTransito) throw new Exception("Status atual da carga não permite conferência");


        $item = CargaTransferItem::where('cargatransferid', '=', $carga->id)->where('etiquetaean13', '=', $ean13)->first();
        if (!$item) throw new Exception("Nenhum volume encontrado com o código");
        if ($item->conferidoentrada === 1) throw new Exception("Volume já foi conferido anteriormente");

      } catch (\Throwable $th) {
        $end_time = microtime(TRUE);
        $time = $end_time - $start_time;

        $ret->msg = $th->getMessage();
        return $ret->toJson();
      }

      try {
        DB::beginTransaction();

        $item->conferidoentrada = 1;
        $item->conferidoentradauserid = $usuario->id;
        $item->conferidoentradadh = Carbon::now();
        $item->conferidoentradauuid = $dispositivo ? $dispositivo->uuid : null;

        $upd = $item->save();

        $carga->totaliza();
        $carga->useridupdated = $usuario->id;
        $carga->updated_at = Carbon::now();
        $upd = $carga->save();

        DB::commit();

        try {
            if ($carga->conferidoentradaprogresso == 100) {
                $retorno = $this->changestatus($carga->id, CargaTransferStatusEnumType::tctEncerrado);
                $retorno = (object)$retorno->getOriginalContent();
                if ($retorno->ok) {
                    $carga = CargaTransfer::find($cargaid);
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

    public function printDetalhe (Request $request, $id)
    {
        $ret = new RetApiController;
        try {
            $disk = Storage::disk('public');

            $usuario = session('usuario');

            $carga = CargaTransfer::find($id);
            if (!$carga) throw new Exception("Carga de transferência não foi encontrada");

            // $acertoinforelviagem = \App\auxiliares\Helper::getConfig('acerto_info_relviagem', '');

            $path = $disk->path('temp');
            if (!$disk->exists('temp')) $disk->makeDirectory('temp');

            $html = view('pdf.cargatransfer.fichadetalhada', compact('carga', 'usuario'))->render();
            $pdf = PDF::loadHtml($html);
            $filename = 'cargatransfer' . md5($html) . '.pdf';

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

    public function item_save(Request $request, $cargaid)
    {
      $ret = new RetApiController;
      try {

        $usuario = session('usuario');
        if (!$usuario) throw new Exception('Nenhum usuário autenticado');

        $carga = CargaTransfer::find($cargaid);
        if (!$carga) throw new Exception("Carga de transferência não foi encontrada");

        $ean13Array = isset($request->ean13) ? $request->ean13 : null;
        if (!$ean13Array) throw new Exception("Nenhum código de barra informado");
        if (count($ean13Array) <= 0) throw new Exception("Nenhum código de barra informado");

        $paletes = Paletes::whereIn('ean13', $ean13Array)
                            ->where('unidadeid', '=', $carga->unidadesaidaid)
                            ->where('status', '=', '2')
                            ->get();
        $paleteids = null;
        if ($paletes) {
          $paleteids = [];
          foreach ($paletes as $palete) {
            $paleteids[] = $palete->id;
          }
        }

        $etiquetas = Etiquetas::whereIn('ean13', $ean13Array)->orWhereIn('paleteid', $paleteids)->get();
        if (!$etiquetas) throw new Exception("Nenhuma etiqueta encontrada");
        if (count($etiquetas) == 0) throw new Exception("Nenhuma etiqueta encontrada");

        } catch (\Throwable $th) {
        $ret->msg = $th->getMessage();
        return $ret->toJson();
      }

      try {
        DB::beginTransaction();

        $erros = [];
        $sucesso = [];
        foreach ($etiquetas as $etiqueta) {
            try {
                if ($etiqueta->travado) throw new Exception("Etiqueta está travada em outra carga");
                if ($etiqueta->unidadeatualid !== $carga->unidadesaidaid) throw new Exception("Etiqueta está em uma unidade diferente da unidade de saída desta carga");

                $cargaItem = new CargaTransferItem();
                $cargaItem->cargatransferid = $carga->id;
                $cargaItem->etiquetaean13 = $etiqueta->ean13;
                $upd = $cargaItem->save();

                $etiqueta->travado = 1;
                $etiqueta->useridupdated = $usuario->id;
                $etiqueta->save();

                $sucesso[] =  [
                    'ean13' => $etiqueta->ean13,
                    'itemid' => $cargaItem->id
                ];

            } catch (\Throwable $th) {
                $erros[] = [
                    'ean13' => $etiqueta->ean13,
                    'erro' => $th->getMessage()
                ];
            }
        }

        if ((count($erros) > 0) && (count($sucesso) === 0)) {
            $ret->data = $erros;
            throw new Exception("Nenhuma etiqueta foi inserida!");
        }

        $carga->useridupdated = $usuario->id;
        $carga->updated_at = Carbon::now();
        $carga->totaliza();
        $upd = $carga->save();

        DB::commit();

        $ret->id = $carga->id;
        $ret->data = [
            'erros' => $erros,
            'sucesso' => $sucesso,
            'carga' => $carga->export(true)
        ];
        $ret->ok = true;
      } catch (\Throwable $th) {
        DB::rollBack();
        $ret->msg = $th->getMessage();
      }


      return $ret->toJson();
    }

    public function item_delete(Request $request, $cargaid)
    {
      $ret = new RetApiController;
      try {

        $usuario = session('usuario');
        if (!$usuario) throw new Exception('Nenhum usuário autenticado');

        $carga = CargaTransfer::find($cargaid);
        if (!$carga) throw new Exception("Carga de transferência não foi encontrada");

        $ids = null;
        if (isset($request->ids)) {
            $ids = explode(",", $request->ids);
            if (!is_array($ids)) $ids[] = $ids;
            $ids = count($ids) > 0 ? $ids : null;
        }
        if (!$ids) throw new Exception("Nenhum id do item informado");
        if (count($ids) === 0) throw new Exception("Nenhum id do item informado");

        $itens = CargaTransferItem::whereIn('id', $ids)->where('cargatransferid', '=', $cargaid)->get();
        if (!$itens) throw new Exception("Item da carga não foi encontrado");
        if (count($itens)==0) throw new Exception("Item da carga não foi encontrado");

      } catch (\Throwable $th) {
        $ret->msg = $th->getMessage();
        return $ret->toJson();
      }

      try {
        DB::beginTransaction();

        foreach ($itens as $item) {

            $etiqueta = $item->etiqueta;
            $etiqueta->travado = 0;
            $etiqueta->useridupdated = $usuario->id;
            $etiqueta->save();

            $item->delete();
        }

        $carga->useridupdated = $usuario->id;
        $carga->updated_at = Carbon::now();
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

}
