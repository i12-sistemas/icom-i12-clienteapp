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

use Illuminate\Support\Facades\Http;

use App\Http\Controllers\RetApiController;

use App\Models\Etiquetas;
use App\Models\EtiquetasLog;

class EtiquetasController extends Controller
{

    public function find(Request $request, $ean)
    {
      $ret = new RetApiController;
      try {

        if (strlen($ean)<13) throw new Exception("EAN da etiqueta inválido");

        $dataset = Etiquetas::where('ean13', '=', $ean)->first();
        if (!$dataset) {
            $logs = EtiquetasLog::where('ean13', '=', $ean)->orderby('created_at', 'DESC')->get();
            if (!$logs) throw new Exception("Etiqueta não foi encontrada");
            if (count($logs) === 0) throw new Exception("Etiqueta não foi encontrada");
            $dadoslogs = [];
            foreach ($logs as $key => $log) {
                $a = $log->export(true);
                $a['nordem'] = $logs->count()-$key;
                $dadoslogs[] = $a;
            }

            $logdelete = EtiquetasLog::where('ean13', '=', $ean)->where('action', '=', 'delete')->where('origem', '=', 'cargaentradaitem')->orderby('created_at', 'DESC')->first();

            $dados = [
                'ean13' => $ean,
                'logdelete' => $logdelete ? $logdelete->export(true) : null,
                'logs' => $dadoslogs
            ];
            $ret->data = $dados;
            $ret->ok = true;
        } else {

            $ret->data = $dataset->export(true);
            $ret->ok = true;
        }



      } catch (\Throwable $th) {
        $ret->msg = $th->getMessage();
      }
      return $ret->toJson();
    }

    public function list(Request $request)
    {
      $ret = new RetApiController;
      try {
        $sortby = isset($request->sortby) ? $request->sortby : 'etiquetas.created_at';
        $descending = isset($request->descending) ? $request->descending : 'asc';

        $ean13 = isset($request->ean13) ? utf8_decode($request->ean13) : null;
        $palete = isset($request->palete) ? utf8_decode($request->palete) : null;
        $unidadeatual = isset($request->unidadeatual) ? utf8_decode($request->unidadeatual) : null;
        $datarefi = isset($request->datarefi) ? $request->datarefi : null;
        $datareff = isset($request->datareff) ? $request->datareff : null;
        $cargaid = isset($request->cargaid) ? intVal($request->cargaid) : null;
        $coletanota = isset($request->coletanota) ? intVal($request->coletanota) : null;
        $unidadeatualid = isset($request->unidadeatualid) ? intval($request->unidadeatualid) : null;

        $desagrupado = isset($request->desagrupado) ? boolVal($request->desagrupado) : false;
        $showall = isset($request->showall) ? boolVal($request->showall) : false;
        $somentedisponivel = isset($request->somentedisponivel) ? boolVal($request->somentedisponivel) : false;

        $find = isset($request->find) ? utf8_decode($request->find) : null;

        $orderby = null;
        if (isset($request->orderby)) {
            $orderby = json_decode($request->orderby,true);
            $orderbynew = [];
            foreach ($orderby as $key => $value) {
                if ($key == 'palete') {
                    $lKey = 'trim(paletes.ean13)';
                } else if ($key == 'motorista') {
                    $lKey = 'trim(motorista.nome)';
                } else if ($key == 'cargaid') {
                    $lKey = 'cargaentradaitem.cargaentradaid';
                } else if ($key == 'unidadeatual') {
                    $lKey = 'unidade.fantasia';
                } else if ($key == 'coletanota') {
                    $lKey = 'cargaentradaitem.nfenumero';
                } else {
                    $lKey = 'etiquetas.' . $key;

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
        $query = Etiquetas::select(DB::raw('etiquetas.*'))
                    ->leftJoin('etiquetas as pai', function($join){
                        $join->on('etiquetas.cargaentradaitem', '=', 'pai.cargaentradaitem');
                        $join->on(\DB::raw('pai.volnum'), '=', \DB::raw(1)) ;
                    })
                    ->leftJoin('cargaentradaitem', 'etiquetas.cargaentradaitem', '=', 'cargaentradaitem.id')
                    ->leftJoin('unidade', 'etiquetas.unidadeatualid', '=', 'unidade.id')
                    ->leftJoin('paletes', 'etiquetas.paleteid', '=', 'paletes.id')
                    ->with( 'cargaentrada', 'itemcargaentrada', 'created_usuario', 'updated_usuario', 'unidadeatual',
                        'etiquetasfilhas', 'ultimolog', 'palete', 'conferidoentrada_usuario' )
                    ->when(isset($request->find)  && ($find ? $find !== '' : false) , function ($query) use ($find) {
                      return $query->where(function($query2) use ($find) {

                        $findInt = intval($find);
                        return $query2->where('cargaentradaitem.nfenumero', '=', $findInt)
                                ->orWhere('cargaentradaitem.cargaentradaid', '=', $findInt)
                                ->orWhere('pai.ean13', 'like', $find . '%')
                                ->orWhere('etiquetas.ean13', 'like', $find . '%')
                                ->orWhere('paletes.ean13', 'like', $find . '%')
                                ->orWhere('unidade.razaosocial', 'like', $find . '%')
                                ->orWhere('unidade.fantasia', 'like', $find . '%')

                                ;
                      });
                    })
                    ->when(isset($request->coletanota) && ($coletanota > 0), function ($query) use ($coletanota)  {
                        return $query->where('cargaentradaitem.nfenumero', '=', $coletanota);
                    })
                    ->when(isset($request->cargaid) && ($cargaid > 0), function ($query) use ($cargaid)  {
                        return $query->where('cargaentradaitem.cargaentradaid', '=', $cargaid);
                    })
                    ->when(isset($request->somentedisponivel) && ($somentedisponivel), function ($query) use ($somentedisponivel)  {
                        return $query->where('etiquetas.travado', '=', 0);
                    })
                    ->when(isset($request->unidadeatualid) && ($unidadeatualid > 0), function ($query) use ($unidadeatualid)  {
                        return $query->where('etiquetas.unidadeatualid', '=', $unidadeatualid);
                    })
                    ->when(isset($request->ean13) && (strlen($ean13) >= 1), function ($query) use ($ean13)  {
                        return $query->where('etiquetas.ean13', 'like', $ean13 . '%');
                    })
                    ->when(isset($request->palete) && (strlen($palete) >= 1), function ($query) use ($palete)  {
                        return $query->where('paletes.ean13', 'like', $palete . '%');
                    })
                    ->when(isset($request->unidadeatual) && ($unidadeatual ? $unidadeatual !== '' : false), function ($query) use ($unidadeatual)  {
                        return $query->where(function($query2) use ($unidadeatual) {
                            return $query2->where('unidade.razaosocial', 'like', '%'.$unidadeatual.'%')
                            ->orWhere('unidade.fantasia', 'like', '%'.$unidadeatual.'%');
                        });
                    })
                    ->when(isset($request->datarefi), function ($query) use ($datarefi) {
                        return $query->Where(DB::Raw('date(etiquetas.dataref)'), '>=', $datarefi);
                    })
                    ->when(isset($request->datareff), function ($query) use ($datareff) {
                        return $query->Where(DB::Raw('date(etiquetas.dataref)'), '<=', $datareff);
                    })
                    ->when($request->orderby, function ($query) use ($orderby) {
                        foreach ($orderby as $key => $value) {
                            $query->orderByRaw($key  . ' ' . $value);
                        }
                        return $query;
                    })
                    ->whereRaw('if(?=1, true, etiquetas.volnum=1)', [$showall])
                    ->groupBy('etiquetas.ean13')
                    ->orderBy('etiquetas.created_at')
                    ->orderBy('etiquetas.cargaentradaitem')
                    ->orderBy('etiquetas.volnum')
                    ;

        $dataset = $query->paginate($perpage);
        $dados = [];

        foreach ($dataset as $row) {
            $dados[] = $row->export();
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


        $dataset = Etiquetas::select(DB::raw('etiquetas.*'))
                    ->leftJoin('cargaentradaitem', 'etiquetas.cargaentradaitem', '=', 'cargaentradaitem.id')
                    ->leftJoin('cargaentrada', 'cargaentradaitem.cargaentradaid', '=', 'cargaentrada.id')
                    ->with( 'itemcargaentrada', 'created_usuario', 'updated_usuario')

                    ->when($request->has('eans') && ($eans ? count($eans) > 0 : false), function ($query) use ($eans)  {
                      return $query->whereIn('etiquetas.ean13', $eans);
                    })
                    ->get()
                    ;
        $eanA = [];
        foreach ($dataset as $key => $row) {
            $eanA[] = $row->ean13;
        }

        $retProc = $this->printEtiquetaInternal($eanA, $output);
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

            // $dataset = Etiquetas::whereIn('etiquetas.ean13', $ean)
            //              ->with('itemcargaentrada')
            //             ->leftJoin('cargaentradaitem', 'etiquetas.cargaentradaitem', '=', 'cargaentradaitem.id')
            //             ->leftJoin('cargaentrada', 'cargaentradaitem.cargaentradaid', '=', 'cargaentrada.id')
            //             ->orderBy('cargaentrada.dhentrada')
            //             ->orderBy('cargaentrada.id')
            //             ->orderBy('etiquetas.cargaentradaitem')
            //             ->orderBy('etiquetas.volnum')
            //             ->get();
            // if (!$dataset) throw new Exception('Nenhuma etiqueta encontrada');

            $sql = 'select coletas_nota.notanumero,coletas_nota.notaserie, etiquetas.volnum, etiquetas.voltotal, usuario.nome as createdusuarionome,
                    etiquetas.ean13, cargaentradaitem.nfechave, cargaentradaitem.coletaid,
                    clienteorigem.razaosocial as origemrazaosocial, clienteorigem.cnpj as origemcnpj,
                    clientedestino.razaosocial as destinorazaosocial, clientedestino.cnpj as destinocnpj,
                    cidadesdestino.cidade as destinocidade,
                    cidadesdestino.uf as destinouf

                    from etiquetas
                    inner join cargaentradaitem on etiquetas.cargaentradaitem=cargaentradaitem.id
                        inner join cargaentrada on cargaentradaitem.cargaentradaid = cargaentrada.id

                    inner join coletas_nota on coletas_nota.id=cargaentradaitem.coletanotaid
                        left join cliente as clienteorigem on clienteorigem.cnpj = coletas_nota.remetentecnpj
                        left join cliente as clientedestino on clientedestino.cnpj = coletas_nota.destinatariocnpj
                        left join cidades as cidadesdestino on cidadesdestino.id = clientedestino.cidadeid

                    left join usuario on usuario.id = etiquetas.useridcreated

                    where etiquetas.ean13 in (' . implode(',', $ean) . ')
                    group by etiquetas.ean13
                    order by cargaentrada.dhentrada asc,
                    cargaentrada.id asc,
                    etiquetas.cargaentradaitem asc,
                    etiquetas.volnum asc';


            $dataset = \DB::select($sql);
            if (!$dataset) throw new Exception('Nenhuma etiqueta encontrada');
            if (count($dataset) === 0) throw new Exception('Nenhuma etiqueta encontrada');


            $path = $disk->path('temp');
            if (!$disk->exists('temp')) $disk->makeDirectory('temp');

            $html = view('pdf.etiquetas.etiqueta-modelo-01-10x10', compact('dataset'))->render();

            $pdf = PDF::loadHtml($html);
            $filename = 'etiquetas-' . md5($html) . '.pdf';

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


    //gerar etiquetas por lote de volume
    public function etiquetas_add_lote($cargaentradaitem, $unidadeatualid, $usuarioid, $volumeinicial, $volumetotal, $pesototal)
    {
      $ret = new RetApiController;
      try {

        if (!$cargaentradaitem) throw new Exception("ID do item da carga de entrada não foi informado");
        if (!($cargaentradaitem > 0)) throw new Exception("ID do item da carga de entrada inválido");

        if (!$usuarioid) throw new Exception("Usuário não foi informado");
        if (!($usuarioid > 0)) throw new Exception("Usuário inválido");

        $volstart = intVal($volumeinicial);
        if (!($volstart > 0)) throw new Exception("Volume inicial deve ser maior do que zero");

        $volmax = intVal($volumetotal);
        if (!($volmax > 0)) throw new Exception("Volume total deve ser maior do que zero");

        if ($volmax < $volstart) throw new Exception("Volume total não pode ser menor do que o volume inicial");


      } catch (\Throwable $th) {
        $ret->msg = $th->getMessage();
        return $ret->toJson();
      }

      try {
        DB::beginTransaction();

        $list = [];
        for ($i=$volstart; $i <= $volmax; $i++) {

            $etiqueta = new Etiquetas();
            $etiqueta->cargaentradaitem = $cargaentradaitem;
            $etiqueta->dataref = Carbon::now();
            $etiqueta->pesototal = $pesototal;
            $etiqueta->status = '1';
            $etiqueta->statusanterior = '1';
            $etiqueta->travado = 1;
            $etiqueta->unidadeatualid = $unidadeatualid;
            $etiqueta->useridcreated = $usuarioid;
            $etiqueta->useridupdated = $usuarioid;
            $etiqueta->volnum = $i;
            $etiqueta->voltotal = $volmax;

            $upd = $etiqueta->save();

            $list[] = $etiqueta->export(true);

        }

        DB::commit();

        $ret->data = $list;
        $ret->ok = true;
      } catch (\Throwable $th) {
        DB::rollBack();
        $ret->msg = $th->getMessage();
      }
      return $ret;
    }

}
