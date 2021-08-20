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

use App\Enums\PaleteStatusEnumType;
use App\Enums\EtiquetasStatusEnumType;
use App\Models\Paletes;
use App\Models\PaletesItem;
use App\Models\Etiquetas;

use App\Http\Traits\EtiquetasTrait;

class PaletesController extends Controller
{
    use EtiquetasTrait;

    public function list(Request $request)
    {
      $ret = new RetApiController;
      try {
        $sortby = isset($request->sortby) ? $request->sortby : 'paletes.dhentrada';
        $descending = isset($request->descending) ? $request->descending : 'asc';

        $created_at = isset($request->created_at) ? $request->created_at : null;
        $created_ati = isset($request->created_ati) ? $request->created_ati : null;
        $created_atf = isset($request->created_atf) ? $request->created_atf : null;

        $ean13 = isset($request->ean13) ? $request->ean13 : null;
        $erros = isset($request->erros) ? $request->erros : null;
        $unidadeid = isset($request->unidadeid) ? intval($request->unidadeid) : null;
        $id = isset($request->id) ? intval($request->id) : null;
        $status = null;
        if (isset($request->status)) {
            $status = explode(",", $request->status);
            if (!is_array($status)) $status[] = $status;
            $status = count($status) > 0 ? $status : null;
        }
        $find = isset($request->find) ? utf8_decode($request->find) : null;
        $descricao = isset($request->descricao) ? utf8_decode($request->descricao) : null;

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
                    $lKey = 'paletes.' . $key;

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
        $query = Paletes::select(DB::raw('paletes.*'))
                    ->leftJoin('unidade', 'paletes.unidadeid', '=', 'unidade.id')
                    ->with( 'unidade', 'itens', 'created_usuario', 'updated_usuario'  )
                    ->when(isset($request->find) && ($find !== ''), function ($query) use ($find) {
                        return $query->where(function($query2) use ($find) {
                          return $query2->where('paletes.descricao', 'like', '%'.$find.'%')
                            ->orWhere('paletes.ean13', 'like', '%'.$find.'%')
                            ->orWhere('unidade.razaosocial', 'like', '%'.$find.'%')
                            ->orWhere('unidade.fantasia', 'like','%'. $find.'%')
                            ;
                        });
                    })
                    ->when(isset($request->descricao) && ($descricao ? $descricao != '' : false), function ($query) use ($descricao)  {
                        return $query->where('paletes.descricao', 'like', '%'.$descricao.'%');
                    })
                    ->when(isset($request->ean13) && ($ean13 ? $ean13 != '' : false), function ($query) use ($ean13)  {
                        return $query->where('paletes.ean13', 'like', '%'.$ean13.'%');
                    })
                    ->when(isset($request->unidadeid) && ($unidadeid > 0), function ($query) use ($unidadeid)  {
                        return $query->where('paletes.unidadeid', '=', $unidadeid);
                    })
                    ->when(isset($request->id) && ($id > 0), function ($query) use ($id)  {
                        return $query->where('paletes.id', '=', $id);
                    })
                    ->when(isset($request->status) && ($status !== ''), function ($query) use ($status)  {
                        return $query->whereIn('paletes.status', $status);
                    })
                    ->when(isset($request->erros) && ($erros !== ''), function ($query) use ($erros)  {
                        if ($erros === '1') {
                            return $query->Where('paletes.erroqtde', '>', 0);
                        } else {
                            return $query->Where('paletes.erroqtde', '=', 0);
                        }
                    })
                    ->when(isset($request->created_at), function ($query) use ($created_at) {
                        return $query->Where(DB::Raw('date(paletes.created_at)'), '=', $created_at);
                    })

                    ->when(isset($request->created_ati), function ($query) use ($created_ati) {
                        return $query->Where(DB::Raw('date(paletes.created_at)'), '>=', $created_ati);
                    })
                    ->when(isset($request->created_atf), function ($query) use ($created_atf) {
                        return $query->Where(DB::Raw('date(paletes.created_at)'), '<=', $created_atf);
                    })

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

                    // ->when(isset($request->ctenumero) && ($ctenumero > 0) , function ($query) use ($ctenumero)  {
                    //     return $query->where('coletas.ctenumero', '=', $ctenumero);
                    // })
                    // ->when(isset($request->ctenumero2) && ($ctenumero2 ? count($ctenumero2) > 0 : false) , function ($query) use ($ctenumero2)  {
                    //     return $query->whereIn('coletas.ctenumero', $ctenumero2);
                    // })
                    // ->when(isset($request->ctenumero2) && ($ctenumero2vazio), function ($query) {
                    //     return $query->whereRaw('ifnull(coletas.ctenumero,"") = ""');
                    // })
                    // ->when(isset($request->ctenumero2) && ($ctenumero2naovazio), function ($query) {
                    //     return $query->whereRaw('ifnull(coletas.ctenumero,"") <> ""');
                    // })


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

        $dataset = Paletes::find($find);
        if (!$dataset) throw new Exception("Palete não foi encontrada");

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
          'descricao' => ['string', 'max:150'],
          'unidadeid' => ['required', 'exists:unidade,id'],
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
          $row = Paletes::find($id);
          if (!$row) throw new Exception("Palete não foi encontrado");
          if ($row->status !== PaleteStatusEnumType::EmAberto) throw new Exception("Não é possível alterar pois o status atual não permite.");
        }

      } catch (\Throwable $th) {
        $ret->msg = $th->getMessage();
        return $ret->toJson();
      }

      try {
        DB::beginTransaction();

        if ($action=='add') {
            $row = new Paletes();
            $row->status = '1';
            $row->useridcreated = $usuario->id;
            $row->volqtde = 0;
            $row->pesototal = 0;
            $row->unidadeid = $request->unidadeid;
        }
        $row->descricao = $request->descricao;
        $row->useridupdated = $usuario->id;
        $row->totaliza();
        $upd = $row->save();

        DB::commit();

        $ret->id = $row->id;
        $ret->data = $row->export(true);
        $ret->ok = true;
      } catch (\Throwable $th) {
        DB::rollBack();
        $ret->msg = $th->getMessage();
      }


      return $ret->toJson();
    }

    public function add_etiquetas(Request $request, $id)
    {
      $ret = new RetApiController;
      try {

        $usuario = session('usuario');
        if (!$usuario) throw new Exception('Nenhum usuário autenticado');

        $row = Paletes::find($id);
        if (!$row) throw new Exception("Palete não foi encontrado");
        if ($row->status !== PaleteStatusEnumType::EmAberto) throw new Exception("Não é possível alterar pois o status atual não permite.");

        $eans = isset($request->eans) ? $request->eans : null;

        if (!$eans) throw new Exception("Nenhum código de barra (EAN) informado");
        if (count($eans) <= 0 ) throw new Exception("Nenhum código de barra (EAN) informado");

        if (!$eans) throw new Exception("Nenhum código de barra informado");
        if (count($eans) <= 0) throw new Exception("Nenhum código de barra informado");

        $etiquetas = Etiquetas::whereIn('ean13', $eans)->get();
        if (!$etiquetas) throw new Exception("Nenhuma etiqueta encontrada");
        if (count($etiquetas) === 0) throw new Exception("Nenhuma etiqueta encontrada");

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
            if ($etiqueta->status !== '1') throw new Exception("Etiqueta deve estar com o status 1=Em Depósito");
            if ($etiqueta->unidadeatualid !== $row->unidadeid) throw new Exception("Etiqueta está em uma unidade diferente da unidade deste palete");
            if ($etiqueta->paleteid) {
              $palete = $etiqueta->palete;
              if ($palete) {
                if (($palete->status == '1') || ($palete->status == '2')) throw new Exception("Etiqueta está no palete EAN " . $palete->ean13);
              }
            }

            $item = new PaletesItem();
            $item->paleteid = $row->id;
            $item->ean13 = $etiqueta->ean13;
            $upd = $item->save();

            $etiqueta->paleteid = $row->id;
            $etiqueta->useridupdated = $usuario->id;
            $etiqueta->save();

            $log = $this->addLog($etiqueta,  $usuario->id, 'paleteitem', $item->id, 'add');
            if (!$log) throw new Exception("Nenhum log da etiqueta foi gerado ao criar item do palete");


            $sucesso[] =  [
                'ean13' => $etiqueta->ean13,
                'itemid' => $item->id
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

        $row->useridupdated = $usuario->id;
        $row->updated_at = Carbon::now();
        $row->totaliza();
        $upd = $row->save();

        DB::commit();

        $ret->data = [
            'erros' => $erros,
            'sucesso' => $sucesso,
            'palete' => $row->export(true)
        ];
        $ret->ok = true;
      } catch (\Throwable $th) {
        DB::rollBack();
        $ret->msg = $th->getMessage();
      }


      return $ret->toJson();
    }

    public function delete_etiquetas(Request $request, $id)
    {
      $ret = new RetApiController;
      try {

        $usuario = session('usuario');
        if (!$usuario) throw new Exception('Nenhum usuário autenticado');

        $row = Paletes::find($id);
        if (!$row) throw new Exception("Palete não foi encontrado");
        if ($row->status !== PaleteStatusEnumType::EmAberto) throw new Exception("Não é possível alterar pois o status atual não permite.");

        $eans = isset($request->eans) ? $request->eans : null;

        if (!$eans) throw new Exception("Nenhum código de barra (EAN) informado");
        if (count($eans) <= 0 ) throw new Exception("Nenhum código de barra (EAN) informado");

        if (!$eans) throw new Exception("Nenhum código de barra informado");
        if (count($eans) <= 0) throw new Exception("Nenhum código de barra informado");

        $itens = PaletesItem::where('paleteid', '=', $row->id)->whereIn('ean13', $eans)->get();
        if (!$itens) throw new Exception("Nenhuma item encontrado");

      } catch (\Throwable $th) {
        $ret->msg = $th->getMessage();
        return $ret->toJson();
      }

      try {
        DB::beginTransaction();

        $erros = [];
        $sucesso = [];
        foreach ($itens as $item) {
          try {
            $etiqueta = $item->etiqueta;
            if ($etiqueta->paleteid === $row->id) $etiqueta->paleteid = null;
            $etiqueta->useridupdated = $usuario->id;
            $etiqueta->save();

            $log = $this->addLog($etiqueta,  $usuario->id, 'paleteitem', $item->id, 'delete');
            if (!$log) throw new Exception("Nenhum log da etiqueta foi gerado ao deletar item do palete");


            $item->delete();

            $sucesso[] =  [
                'ean13' => $etiqueta->ean13
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
            throw new Exception("Nenhuma etiqueta foi excluida!");
        }

        $row->useridupdated = $usuario->id;
        $row->updated_at = Carbon::now();
        $row->totaliza();
        $upd = $row->save();

        DB::commit();

        $ret->data = [
            'erros' => $erros,
            'sucesso' => $sucesso,
            'palete' => $row->export(true)
        ];
        $ret->ok = true;
      } catch (\Throwable $th) {
        DB::rollBack();
        $ret->msg = $th->getMessage();
      }


      return $ret->toJson();
    }

    public function changestatus(Request $request, $id)
    {
      $ret = new RetApiController;
      try {
        $usuario = session('usuario');
        if (!$usuario) throw new Exception('Nenhum usuário autenticado');

        $palete = Paletes::find($id);
        if (!$palete) throw new Exception("Carga de entrega não foi encontrada");
        $palete->totaliza();

        $status = isset($request->status) ? $request->status : null;
        if (!$status) throw new Exception("Nenhum status informado");

        if ($palete->status == PaleteStatusEnumType::Cancelado) throw new Exception("Palete cancelado! Não é possível alterar o status.");
        if ($palete->status == PaleteStatusEnumType::Despachado) throw new Exception("Palete despachado! Não é possível alterar o status.");

        if ($status == PaleteStatusEnumType::Lacrado) {
            if (!$palete->itens) throw new Exception("Não é possível lacrar o palete com carga vazia");
            if (!($palete->itens->count() > 0)) throw new Exception("Não é possível lacrar o palete com carga vazia");
            if ($palete->erroqtde > 0) throw new Exception("Não é possível lacrar o palete com erros");
        }


      } catch (\Throwable $th) {
        $ret->msg = $th->getMessage();
        return $ret->toJson();
      }

      try {
        DB::beginTransaction();

        foreach ($palete->itens as $item) {
            $clear = ($status === PaleteStatusEnumType::Cancelado) || ($status === PaleteStatusEnumType::Despachado);

            $etiqueta = $item->etiqueta;
            if (($clear) && ($etiqueta->paleteid === $palete->id)) $etiqueta->paleteid = null;
            $etiqueta->useridupdated = $usuario->id;
            $etiqueta->save();

            $log = $this->addLog($etiqueta, $usuario->id, 'paleteitem', $item->id, 'update',
                        'Alteração de status do palete de ' . $palete->status . '-' . PaleteStatusEnumType::getDescription($palete->status) .
                        ' para ' . $status . '-' . PaleteStatusEnumType::getDescription($status)
                    );
            if (!$log) throw new Exception("Nenhum log da etiqueta foi gerado ao atualizar status do palete");
        }


        $palete->useridupdated = $usuario->id;
        $palete->status = $status;
        $upd = $palete->save();

        DB::commit();

        $ret->id = $palete->id;
        $ret->data = $palete->export(true);
        $ret->ok = true;
      } catch (\Throwable $th) {
        DB::rollBack();
        $ret->msg = $th->getMessage();
      }
      return $ret->toJson();
    }

    public function printEtiqueta(Request $request)
    {
      $ret = new RetApiController;
      try {
        $output = isset($request->output) ? $request->output : '';

        $eans = null;
        if ($request->has('eans')) {
          $eans = explode(",", $request->eans);
          if (!is_array($eans)) $eans[] = $eans;
          $eans = count($eans) > 0 ? $eans : null;
        }

        $dataset = Paletes::whereIn('ean13', $eans)->get();

        $eanA = [];
        foreach ($dataset as $key => $row) {
            $eanA[] = $row->ean13;
        }

        $retProc = $this->printEtiquetaInternal($eanA, $output);

        if ($output === 'teste') return $retProc;
        if (!$retProc->ok) throw new Exception($retProc->msg);

        $ret = $retProc;
        $ret->ok = true;
      } catch (\Throwable $th) {
        $ret->msg = $th->getMessage();
      }
      return $ret->toJson();
    }


    //internal user
    public function printEtiquetaInternal ($eanArray, $output = '')
    {
        $ret = new RetApiController;
        try {
            $disk = Storage::disk('public');

            if (is_array($eanArray)) {
                $ean = $eanArray;
            } else {
                $ean = explode(",", $eanArray);
                if (!is_array($ean)) $ean[] = $ean;
                $ean = count($ean) > 0 ? $ean : null;
            }



            if (!$ean) throw new Exception('Nenhum código de barra informado');
            if (count($ean) == 0) throw new Exception('Nenhum código de barra informado');

            $dataset = Paletes::whereIn('ean13', $ean)->get();
            if (!$dataset) throw new Exception('Nenhuma etiqueta encontrada');

            $path = $disk->path('temp');
            if (!$disk->exists('temp')) $disk->makeDirectory('temp');

            $html = view('pdf.paletes.palete-etiqueta-modelo-01-10x10', compact('dataset'))->render();

            $pdf = PDF::loadHtml($html);
            $filename = 'paletes-etiquetas-' . md5($html) . '.pdf';

            $file = 'temp/' . $filename;

            if (!$disk->exists($file)) $disk->delete($file);
            $pdf->save($disk->path($file));

            if (!$disk->exists('temp/' . $filename))
                throw new Exception('Falha ao gerar PDF. Arquivo não foi encontrado no disco.');


            if ($output == 'teste') {
                return $disk->download('temp/' . $filename, $filename, [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'inline; filename="'.$filename.'"'
                    ]);
            }


            if ($output == 'localfile') $ret->data = $file;
            $ret->msg = $disk->url($file);
            $ret->ok = true;
        } catch (\Throwable $th) {
            $ret->msg = $th->getMessage();
        }
        return $ret;
    }

    public function delete(Request $request, $id)
    {
      $ret = new RetApiController;
      try {

        $usuario = session('usuario');
        if (!$usuario) throw new Exception('Nenhum usuário autenticado');

        $row = Paletes::find($id);
        if (!$row) throw new Exception("Palete não foi encontrado");
        if ($row->status !== PaleteStatusEnumType::EmAberto) throw new Exception("Não é possível excluir pois o status atual não permite.");

        $eans = isset($request->eans) ? $request->eans : null;

        // $itens = PaletesItem::where('paleteid', '=', $row->id)->whereIn('ean13', $eans)->get();
        // if (!$itens) throw new Exception("Nenhuma item encontrado");

      } catch (\Throwable $th) {
        $ret->msg = $th->getMessage();
        return $ret->toJson();
      }

      try {
        DB::beginTransaction();

        // $erros = [];
        $itens = $row->itens;
        foreach ($itens as $item) {
          try {
            $etiqueta = $item->etiqueta;
            if ($etiqueta->paleteid === $row->id) $etiqueta->paleteid = null;
            $etiqueta->useridupdated = $usuario->id;
            $etiqueta->save();

            $log = $this->addLog($etiqueta,  $usuario->id, 'paleteitem', $item->id, 'delete');
            if (!$log) throw new Exception("Nenhum log da etiqueta foi gerado ao deletar item do palete");

            $item->delete();

          } catch (\Throwable $th) {
            throw new Exception("Falha ao excluir etiqueta " .  $etiqueta->ean13 . " - " . $etiqueta->ean13);
          }
        }

        $upd = $row->delete();

        DB::commit();

        $ret->ok = true;
      } catch (\Throwable $th) {
        DB::rollBack();
        $ret->msg = $th->getMessage();
      }


      return $ret->toJson();
    }



}
