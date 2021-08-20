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

use App\Enums\CargaEntregaStatusEnumType;
use App\Enums\EtiquetasStatusEnumType;
use App\Models\CargaEntrega;
use App\Models\CargaEntregaItem;
use App\Models\Etiquetas;
use App\Models\Paletes;
use App\Models\CargaEntregaBaixaImg;

use App\Http\Traits\EtiquetasTrait;

class CargaEntregaController extends Controller
{
    use EtiquetasTrait;

    public function list(Request $request)
    {
      $ret = new RetApiController;
      try {
        $sortby = isset($request->sortby) ? $request->sortby : 'cargaentrega.dhentrada';
        $descending = isset($request->descending) ? $request->descending : 'asc';
        $created_at = isset($request->created_at) ? $request->created_at : null;
        $created_ati = isset($request->created_ati) ? $request->created_ati : null;
        $created_atf = isset($request->created_atf) ? $request->created_atf : null;

        $id = isset($request->id) ? intVal($request->id) : null;
        $erros = isset($request->erros) ? $request->erros : null;
        $unidadesaidaid = isset($request->unidadesaidaid) ? $request->unidadesaidaid : null;
        $status = null;
        if (isset($request->status)) {
            $status = explode(",", $request->status);
            if (!is_array($status)) $status[] = $status;
            $status = count($status) > 0 ? $status : null;
        }
        $find = isset($request->find) ? utf8_decode($request->find) : null;
        $motoristastr = isset($request->motoristastr) ? utf8_decode($request->motoristastr) : null;
        $veiculostr = isset($request->veiculostr) ? utf8_decode($request->veiculostr) : null;

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
                    $lKey = 'cargaentrega.' . $key;

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
        $query = CargaEntrega::select(DB::raw('cargaentrega.*'))
                    ->leftJoin('unidade', 'cargaentrega.unidadesaidaid', '=', 'unidade.id')
                    ->leftJoin('veiculo', 'cargaentrega.veiculoid', '=', 'veiculo.id')
                    ->leftJoin('motorista', 'cargaentrega.motoristaid', '=', 'motorista.id')
                    ->with( 'unidadesaida', 'motorista', 'veiculo'  )
                    ->when(isset($request->find) && ($find !== ''), function ($query) use ($find) {
                        return $query->where(function($query2) use ($find) {
                          return $query2->where('veiculo.placa', 'like', '%'.cleanDocMask($find).'%')

                            ->orWhere('motorista.nome', 'like', '%'.$find.'%')
                            ->orWhere('motorista.apelido', 'like','%'. $find.'%')

                            ->orWhere('unidade.razaosocial', 'like', '%'.$find.'%')
                            ->orWhere('unidade.fantasia', 'like','%'. $find.'%')
                            ;
                        });
                      })
                    ->when(isset($request->unidadesaidaid) && ($unidadesaidaid > 0), function ($query) use ($unidadesaidaid)  {
                        return $query->where('cargaentrega.unidadesaidaid', '=', $unidadesaidaid);
                    })
                    ->when(isset($request->status) && ($status !== ''), function ($query) use ($status)  {
                        return $query->whereIn('cargaentrega.status', $status);
                    })
                    ->when(isset($request->erros) && ($erros !== ''), function ($query) use ($erros)  {
                        if ($erros === '1') {
                            return $query->Where('cargaentrega.erroqtde', '>', 0);
                        } else {
                            return $query->Where('cargaentrega.erroqtde', '=', 0);
                        }
                    })
                    ->when(isset($request->created_at), function ($query) use ($created_at) {
                        return $query->Where(DB::Raw('date(cargaentrega.created_at)'), '=', $created_at);
                    })

                    ->when(isset($request->created_ati), function ($query) use ($created_ati) {
                        return $query->Where(DB::Raw('date(cargaentrega.created_at)'), '>=', $created_ati);
                    })
                    ->when(isset($request->created_atf), function ($query) use ($created_atf) {
                        return $query->Where(DB::Raw('date(cargaentrega.created_at)'), '<=', $created_atf);
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

                    ->when(isset($request->id) && ($id > 0), function ($query) use ($id)  {
                        return $query->where('cargaentrega.id', '=', $id);
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

        $dataset = CargaEntrega::find($find);
        if (!$dataset) throw new Exception("Carga de entrega não foi encontrada");

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
            $carga = CargaEntrega::find($id);
            if (!$carga) throw new Exception("Carga de entrega não foi encontrada");
            if ($carga->itens->count() > 0) throw new Exception("Carga bloqueada pois contêm itens");
        }



      } catch (\Throwable $th) {
        $ret->msg = $th->getMessage();
        return $ret->toJson();
      }

      try {
        DB::beginTransaction();

        if ($action=='add') {
            $carga = new CargaEntrega();
            $carga->status = '1';
            $carga->useridcreated = $usuario->id;
            $carga->volqtde = 0;
            $carga->peso = 0;
            $carga->erroqtde = 0;
        }
        $carga->unidadesaidaid = $request->unidadesaidaid;
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

    public function entregaBaixa(Request $request)
    {
      $ret = new RetApiController;
      try {

        $usuario = session('usuario');
        if (!$usuario) throw new Exception('Nenhum usuário autenticado');

        $tipo = isset($request->tipo) ? $request->tipo : null;
        if (!$tipo) throw new Exception("Tipo de baixa não informado");
        if (!(($tipo === 'cte') || ($tipo === 'carga'))) throw new Exception("Tipo de baixa informado (" . $tipo . ") inválido.");

        $operacao = isset($request->operacao) ? $request->operacao : null;
        if (!$operacao) throw new Exception("Operação de baixa não informado");
        if (!(($operacao === 'A') || ($operacao === 'M'))) throw new Exception("Operação de baixa informado (" . $operacao . ") inválido.");

        if ($tipo === 'cte') {
            $ctechave = isset($request->ctechave) ? $request->ctechave : null;
            if (!$ctechave) throw new Exception("Chave do CT-e não informado");
            if (strlen($ctechave) !== 44) throw new Exception("Chave do CT-e não inválida");

            $item = CargaEntregaItem::where('ctechave', '=', $ctechave)->first();
            if (!$item) throw new Exception("Nenhuma carga encontrada com o CT-e informado");
            $carga = $item->cargaentrega;
            if (!$carga) throw new Exception("Nenhuma carga encontrada com o CT-e informado");
            if ($carga->status === '4') throw new Exception("Carga já foi entregue!");
            if ($carga->status !== '3') throw new Exception("Status atual da carga não permite baixa de entrega");
            if ($carga->itens->count() <= 0) throw new Exception("Carga informada não tem  volume");

            $check = CargaEntregaBaixaImg::where('ctechave', '=', $ctechave)->first();
            if ($check) throw new Exception("A carga deste CT-e já foi baixada em " . $check->baixadhlocal->format('d/m/Y'));
        }
        if ($tipo === 'carga') {
            $cargarecebida = isset($request->carga) ? json_decode($request->carga) : null;
            if (!$cargarecebida) throw new Exception("Carga não informada");
            if (!$cargarecebida->cargaid) throw new Exception("Carga não informada");
            $carga = CargaEntrega::find($cargarecebida->cargaid);
            if (!$carga) throw new Exception("Nenhuma carga encontrada");
            if ($carga->status === '4') throw new Exception("Carga já foi entregue!");
            if ($carga->status !== '3') throw new Exception("Status atual da carga não permite baixa de entrega");
            if ($carga->itens->count() <= 0) throw new Exception("Carga informada não tem  volume");
            if (mb_strtoupper($carga->senha) !== mb_strtoupper($cargarecebida->senha)) throw new Exception("Senha da carga está errada!");

            $check = CargaEntregaBaixaImg::where('cargaentregaid', '=', $carga->id)->where('tipo', '=', 'carga')->first();
            if ($check) throw new Exception("Esta carga foi baixada em " . $check->baixadhlocal->format('d/m/Y'));
        }

        // save imagem
        try {
            $arquivo = $request->arquivo;
            $arquivo = str_replace('data:image/jpeg;base64,', '', $arquivo);
            $arquivo = str_replace(' ', '+', $arquivo);
            $arquivo = base64_decode($arquivo);
            if (!$arquivo) throw new Exception('Nenhum arquivo enviado');
            $ext = '.jpg';
            $md5 = md5($request->file('arquivo'));
            $path = 'cargaentrega/comprovante/' . Carbon::now()->format('Y-m-d') . '/';
            $file = $tipo . '-' . ($tipo === 'cte' ? $ctechave : $carga->cargaid) . '-' . Carbon::now()->format('h-i-s') . $ext;
            $fullnamefile = $path . $file;
            $disk = Storage::disk();
            $checkarquivo = $disk->exists($fullnamefile);
            if ($checkarquivo) $disk->delete($fullnamefile);;
            if (!$disk->exists($path)) $disk->makeDirectory($path);
            $disk->put($fullnamefile, $arquivo);
            if (!$disk->exists($fullnamefile)) throw new Exception('Arquivo não foi salvo');
            $size = $disk->size($fullnamefile);
        } catch (\Throwable $th) {
            throw new Exception("Falha ao salvar imagem :: " . $th->getMessage());
        }
        // save imagem

      } catch (\Throwable $th) {
        $ret->msg = $th->getMessage();
        return $ret->toJson();
      }

      try {
        DB::beginTransaction();

        $baixa = new CargaEntregaBaixaImg();
        $baixa->cargaentregaid = $carga->id;
        $baixa->tipo = $tipo;
        if ($tipo === 'cte') {
            $baixa->ctechave = $ctechave;
        }

        $baixa->operacao = $operacao;
        $baixa->origem = '1';
        // $baixa->uuid = '1';
        // $baixa->motoritstaid = '1';

        $baixa->usuarioid = $usuario->id;
        $baixa->baixadhlocal = Carbon::now();
        $baixa->created_at = Carbon::now();

        $baixa->imglocal = 'local';
        $baixa->imgfullname = $fullnamefile;
        $baixa->imgext = $ext;
        $baixa->imgmd5 = $md5;
        $baixa->imgsize = $size;
        $upd = $baixa->save();

        if ($tipo === 'cte') {
            foreach ($baixa->itensdecarga as $item) {
                $item->entreguedh = Carbon::now();
                $item->entregueoperacao = $operacao;
                $item->entregue = 1;
                $item->save();
            }
            $carga = CargaEntrega::find($carga->id);
            $carga->entregatipo = '2';
            $carga->entregaoperacao = $operacao;
            $carga->totaliza();
            $carga->save();
        }
        if ($tipo === 'carga') {
            foreach ($carga->itens as $item) {
                $item->entreguedh = Carbon::now();
                $item->entregueoperacao = $operacao;
                $item->entregue = 1;
                $item->save();
            }

            $carga = CargaEntrega::find($carga->id);
            $carga->entregaoperacao = $operacao;
            $carga->entregatipo = '1';
            $carga->totaliza();
            $carga->save();
        }

        DB::commit();

        $ret->id = $baixa->id;
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

        $carga = CargaEntrega::find($id);
        if (!$carga) throw new Exception("Carga de entrega não foi encontrada");
        $carga->totaliza();

        $status = isset($request->status) ? $request->status : null;
        if (!$status) throw new Exception("Nenhum status informado");

        // se diferente de 1=Em aberto
        if ($status !== CargaEntregaStatusEnumType::tceEmAberto) {
            if (!$carga->itens) throw new Exception("Não é possível alterar o status de uma carga vazia");
            if (!($carga->itens->count() > 0)) throw new Exception("Não é possível alterar o status de uma carga vazia");

            if ($carga->erroqtde > 0) throw new Exception("Não é possível alterar o status de uma carga com erros");
        }

      } catch (\Throwable $th) {
        $ret->msg = $th->getMessage();
        return $ret->toJson();
      }

      try {
        DB::beginTransaction();


        if (($status === CargaEntregaStatusEnumType::tceEmAberto) || ($status === CargaEntregaStatusEnumType::tceLiberadoCarregarEntrega)) {
            $carga->saidauserid = null;
            $carga->saidadh = null;

            $carga->entregatipo = null;
            $carga->entregaultimadh = null;
            $carga->entregaqtdeitem = 0;
            $carga->entregapercentual = 0;
        }
        if ($status === CargaEntregaStatusEnumType::tceEmTransito) {
            if (($carga->status === CargaEntregaStatusEnumType::tceEmAberto) || ($carga->status === CargaEntregaStatusEnumType::tceLiberadoCarregarEntrega)) {
                $carga->saidauserid = $usuario->id;
                $carga->saidadh = Carbon::now();
            }
            $carga->entregatipo = null;
            $carga->entregaultimadh = null;
            $carga->entregaqtdeitem = 0;
            $carga->entregapercentual = 0;
        }


        foreach ($carga->itens as $item) {
            $etiqueta = $item->etiqueta;

            $etiqueta->travado = 1;
            if ($status === CargaEntregaStatusEnumType::tceEntregue) {
                $etiqueta->status = EtiquetasStatusEnumType::Entregue;
            } else if ($status === CargaEntregaStatusEnumType::tceEmTransito) {
                $etiqueta->status = EtiquetasStatusEnumType::EmEntrega;
            } else {
                $etiqueta->status = EtiquetasStatusEnumType::EmDeposito;
            }
            $etiqueta->useridupdated = $usuario->id;
            $etiqueta->save();

            $log = $this->addLog($etiqueta, $usuario->id, 'cargaentregaitem', $item->id, 'update',
                        'Alteração de status da carda de ' . $carga->status . '-' . CargaEntregaStatusEnumType::getDescription($carga->status) .
                        ' para ' . $status . '-' . CargaEntregaStatusEnumType::getDescription($status)
                    );
            if (!$log) throw new Exception("Nenhum log da etiqueta foi gerado ao criar etiqueta");
        }

        if ($status === CargaEntregaStatusEnumType::tceEntregue) {
            $carga->entregatipo = '1';
            $carga->entregaultimadh = Carbon::now();
            $carga->entregaqtdeitem = 0;
            $carga->entregapercentual = 100;
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

    public function printDetalhe (Request $request, $id)
    {
        $ret = new RetApiController;
        try {
            $disk = Storage::disk('public');

            $usuario = session('usuario');

            $carga = CargaEntrega::find($id);
            if (!$carga) throw new Exception("Carga de entrega não foi encontrada");

            // $acertoinforelviagem = \App\auxiliares\Helper::getConfig('acerto_info_relviagem', '');

            $path = $disk->path('temp');
            if (!$disk->exists('temp')) $disk->makeDirectory('temp');

            // QrCode::size(500)
            // // ->merge('images/laravel.png', 0.5, true)
            // ->generate('codingdriver.com', storage_path('app\images\qrcode.png'));


            $html = view('pdf.cargaentrega.fichadetalhada', compact('carga', 'usuario'))->render();
            $pdf = PDF::loadHtml($html);
            $filename = 'cargaentrega' . md5($html) . '.pdf';

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

    public function item_add(Request $request, $cargaid)
    {
      $ret = new RetApiController;
      try {

        $usuario = session('usuario');
        if (!$usuario) throw new Exception('Nenhum usuário autenticado');

        $carga = CargaEntrega::find($cargaid);
        if (!$carga) throw new Exception("Carga de entrega não foi encontrada");

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

                $cargaItem = new CargaEntregaItem();
                $cargaItem->cargaentregaid = $carga->id;
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

    public function item_update(Request $request, $cargaid)
    {
      $ret = new RetApiController;
      try {

        $usuario = session('usuario');
        if (!$usuario) throw new Exception('Nenhum usuário autenticado');

        $carga = CargaEntrega::find($cargaid);
        if (!$carga) throw new Exception("Carga de entrega não foi encontrada");

        $ids = isset($request->ids) ? $request->ids : null;
        if (!$ids) throw new Exception("Nenhum id informado");
        if (!is_array($ids)) throw new Exception("IDs informado fora do padrão (array)");
        if (count($ids) <= 0) throw new Exception("Nenhum id informado");

        $itens = CargaEntregaItem::where('cargaentregaid', '=', $carga->id)->whereIn('id', $ids)->get();
        if (!$itens) throw new Exception("Nenhum item encontrado");

        $ctecnpj = null;
        $ctenumero = null;
        $ctechave = isset($request->ctechave) ? trim($request->ctechave) : '';
        if (strlen($ctechave) > 0) {
            if (!testaChaveNFe($ctechave)) throw new Exception("Número da chave do CT-e é inválida");
            $ch = decodeChaveNFe($ctechave);
            $ctecnpj = $ch['CNPJ'];
            $ctenumero = $ch['nNF'];
        } else {
            $ctechave = null;
        }

        $checkqtde = CargaEntregaItem::where('ctechave', '=', $ctechave)->where('cargaentregaid', '!=', $carga->id)->count();
        if ($checkqtde > 0) throw new Exception("Essa chave de CT-e já esta associada a outra carga");

      } catch (\Throwable $th) {
        $ret->msg = $th->getMessage();
        return $ret->toJson();
      }

      try {
        DB::beginTransaction();



        foreach ($itens as $key => $item) {

            $msg = 'Atualização dos dados da chave de CT-e de ' . ($item->ctechave ? $item->ctechave : '*Vazio*') . ' para ' . ($ctechave ? $ctechave : '*Vazio*');

            $log = $this->addLog($item->etiqueta, $usuario->id, 'cargaentregaitem', $item->id, 'update', $msg);
            if (!$log) throw new Exception("Nenhum log da etiqueta foi gerado ao criar item da carga de entrega");


            $item->ctechave = $ctechave;
            $item->ctecnpj = $ctecnpj;
            $item->ctenumero = $ctenumero;
            $item->save();
        }

        $carga = CargaEntrega::find($carga->id);
        $carga->useridupdated = $usuario->id;
        $carga->updated_at = Carbon::now();
        $carga->totaliza();
        $upd = $carga->save();

        DB::commit();

        $ret->data = $carga->export(true);
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

        $carga = CargaEntrega::find($cargaid);
        if (!$carga) throw new Exception("Carga de entrega não foi encontrada");

        $ids = null;
        if (isset($request->ids)) {
            $ids = explode(",", $request->ids);
            if (!is_array($ids)) $ids[] = $ids;
            $ids = count($ids) > 0 ? $ids : null;
        }
        if (!$ids) throw new Exception("Nenhum id do item informado");
        if (count($ids) === 0) throw new Exception("Nenhum id do item informado");

        $itens = CargaEntregaItem::whereIn('id', $ids)->where('cargaentregaid', '=', $cargaid)->get();
        if (!$itens) throw new Exception("Item da carga não foi encontrado");
        if (count($itens)==0) throw new Exception("Item da carga não foi encontrado");

      } catch (\Throwable $th) {
        $ret->msg = $th->getMessage();
        return $ret->toJson();
      }

      try {
        DB::beginTransaction();

        $carga->useridupdated = $usuario->id;
        $carga->updated_at = Carbon::now();

        foreach ($itens as $item) {

            $etiqueta = $item->etiqueta;
            $etiqueta->travado = 0;
            $etiqueta->useridupdated = $usuario->id;
            $etiqueta->save();

            $item->delete();
        }

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
