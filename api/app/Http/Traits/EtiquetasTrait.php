<?php

namespace App\Http\Traits;
use App\Models\Etiquetas;
use App\Models\EtiquetasLog;
use App\Models\CargaEntradaItem;
use App\Models\CargaTransferItem;
use App\Models\CargaEntregaItem;
use App\Models\PaletesItem;
use App\Enums\PaleteStatusEnumType;
use Carbon\Carbon;

trait EtiquetasTrait {

    public function addLog(Etiquetas $etiqueta, $useridcreated, $origem, $origemid, $action, $detalhe = '') {

        $detalheInterno = [];
        switch ($origem) {
            case 'cargaentradaitem':
                $item = CargaEntradaItem::find($origemid);
                if ($item) {
                    $detalheInterno = [
                        'detalhe' => $detalhe,
                        'cargaid' => $item->cargaentrada->id,
                        'cargadataentrada' => $item->cargaentrada->dhentrada->format('Y-m-d H:i'),
                        'coletaid' => $item->coletaid,
                        'nfechave' => $item->nfechave,
                        'tipoprocessamento' => $item->tipoprocessamento,
                        'errors' => $item->errors
                    ];
                }
                break;
            case 'cargatransferitem':
                $item = CargaTransferItem::find($origemid);
                if ($item) {
                    $detalheInterno = [
                        'detalhe' => $detalhe,
                        'cargaid' => $item->cargatransfer->id,
                        'cargacreated_at' => $item->cargatransfer->created_at->format('Y-m-d H:i'),
                        'unidadesaida' => $item->cargatransfer->unidadesaida ? $item->cargatransfer->unidadesaida->toSimple() : null,
                        'unidadeentrada' => $item->cargatransfer->unidadeentrada ? $item->cargatransfer->unidadeentrada->toSimple() : null,
                        'motorista' => $item->cargatransfer->motorista ? $item->cargatransfer->motorista->exportsmall() : null,
                        'veiculo' => $item->cargatransfer->veiculo ? $item->cargatransfer->veiculo->toObject(false) : null
                    ];
                }
                break;
            case 'cargaentregaitem':
                $item = CargaEntregaItem::find($origemid);
                if ($item) {
                    $detalheInterno = [
                        'detalhe' => $detalhe,
                        'cargaid' => $item->cargaentrega->id,
                        'cargacreated_at' => $item->cargaentrega->created_at->format('Y-m-d H:i'),
                        'cargasaidadh' => $item->cargaentrega->saidadh ? $item->cargaentrega->saidadh->format('Y-m-d H:i:s') : null,
                        'entregapercentual' => $item->cargaentrega->entregapercentual ? $item->cargaentrega->entregapercentual : null,
                        'unidadesaida' => $item->cargaentrega->unidadesaida ? $item->cargaentrega->unidadesaida->toSimple() : null,
                        'motorista' => $item->cargaentrega->motorista ? $item->cargaentrega->motorista->exportsmall() : null,
                        'veiculo' => $item->cargaentrega->veiculo ? $item->cargaentrega->veiculo->toObject(false) : null
                    ];
                }
                break;
            case 'paleteitem':
                $item = PaletesItem::find($origemid);
                if ($item) {
                    $detalheInterno = [
                        'detalhe' => $detalhe,
                        'paleteid' => $item->palete->id,
                        'paletecreated_at' => $item->palete->created_at->format('Y-m-d H:i:s'),
                        'paletedescricao' => $item->palete->descricao,
                        'unidade' => $item->palete->unidade ? $item->palete->unidade->toSimple() : null,
                        'volqtde' => $item->palete->volqtde,
                        'pesototal' => $item->palete->pesototal,
                        'status' => $item->palete->status . '-' . PaleteStatusEnumType::getDescription($item->palete->status),
                    ];
                }
                break;

            default:
                # code...
                break;
        }

        $log = new EtiquetasLog();
        $log->ean13 = $etiqueta->ean13;
        $log->created_at = Carbon::now();
        $log->useridcreated = $useridcreated;
        $log->origem = $origem;
        $log->origemid = $origemid;
        $log->action = $action;
        $log->detalhe = \json_encode($detalheInterno);
        $log->save();

        $etiqueta->logatualid = $log->id;
        $upd = $etiqueta->save();

        return $log;
    }
}
