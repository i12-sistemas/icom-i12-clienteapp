<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Etiquetas;
use Illuminate\Support\Facades\DB;

class CargaTransfer extends Model
{
    protected $table = 'cargatransfer';
    protected $dates = ['saidadh', 'entradadh', 'created_at', 'updated_at'];
    public $timestamps = true;

    public function export($complete = true)
    {
        $dados = $this->toArray();
        $dados['entradadh'] = $this->entradadh ? $this->entradadh->format('Y-m-d H:i:s') : null;
        $dados['saidadh'] = $this->saidadh ? $this->saidadh->format('Y-m-d H:i:s') : null;
        $dados['created_at'] = $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null;
        $dados['updated_at'] = $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null;

        $dados['unidadesaida'] = $this->unidadesaida ? $this->unidadesaida->toSimple() : null;
        $dados['unidadeentrada'] = $this->unidadeentrada ? $this->unidadeentrada->toSimple() : null;
        $dados['motorista'] = $this->motorista ? $this->motorista->exportsmall() : null;
        $dados['veiculo'] = $this->veiculo ? $this->veiculo->toObject(false) : null;

        if ($this->status !== '1') $dados['saida_usuario'] = $this->saida_usuario ? $this->saida_usuario->toSimple() : null;
        if ($this->status === '4') $dados['entrada_usuario'] = $this->entrada_usuario ? $this->entrada_usuario->toSimple() : null;

        if ($complete) {
            $dados['created_usuario'] = $this->created_usuario ? $this->created_usuario->toSimple() : null;
            $dados['updated_usuario'] = $this->updated_usuario ? $this->updated_usuario->toSimple() : null;

            $aItens = [];
            foreach ($this->itens as $e) {
                $aItens[] = $e->export(true);
            }
            $dados['itens'] = $aItens;
        }

        unset($dados['unidadesaidaid']);
        unset($dados['unidadeentradaid']);
        unset($dados['entradauserid']);
        unset($dados['saidauserid']);
        unset($dados['motoristaid']);
        unset($dados['veiculoid']);
        unset($dados['useridcreated']);
        unset($dados['useridupdated']);

        return $dados;
    }

    public function itens()
    {
        return $this->hasMany(CargaTransferItem::class, 'cargatransferid', 'id')
                        ->select(\DB::raw('cargatransferitem.*'))
                        ->join('etiquetas', 'cargatransferitem.etiquetaean13', '=', 'etiquetas.ean13')
                        ->orderBy('etiquetas.cargaentradaitem')
                        ->orderBy('etiquetas.volnum')
                        ->orderBy('etiquetas.voltotal')
                        ->with('etiqueta');
    }

    public function created_usuario()
    {
        return $this->hasOne(Usuario::class, 'id', 'useridcreated');
    }

    public function updated_usuario()
    {
        return $this->hasOne(Usuario::class, 'id', 'useridupdated');
    }


    public function saida_usuario()
    {
        return $this->hasOne(Usuario::class, 'id', 'saidauserid');
    }

    public function entrada_usuario()
    {
        return $this->hasOne(Usuario::class, 'id', 'entradauserid');
    }

    public function unidadeentrada()
    {
        return $this->hasOne(Unidade::class, 'id', 'unidadeentradaid')->with('cidade');
    }

    public function unidadesaida()
    {
        return $this->hasOne(Unidade::class, 'id', 'unidadesaidaid')->with('cidade');
    }

    public function motorista()
    {
        return $this->hasOne(Motorista::class, 'id', 'motoristaid');
    }

    public function veiculo()
    {
        return $this->hasOne(Veiculo::class, 'id', 'veiculoid');
    }


    public function geterromsgAttribute($value)
    {
      return utf8_encode($value);
    }
    public function seterromsgAttribute($value)
    {
      $this->attributes['erromsg'] =  utf8_decode($value);
    }

    public function totaliza()
    {
        $qtdeitens = $this->itens->count();
        $peso = 0;
        $etiquetasAssociadas = $this->itens()->with('etiqueta')->get();
        foreach ($etiquetasAssociadas as $key => $item) {
            $peso = $peso + $item->etiqueta->pesovol;
        }

        $grupo = $this->itens()->with('etiqueta')
                        ->groupBy('etiquetas.cargaentradaitem')
                        ->groupBy('etiquetas.voltotal')
                        ->orderBy('etiquetas.cargaentradaitem')
                        ->orderBy('etiquetas.volnum')
                        ->orderBy('etiquetas.voltotal')
                        ->get();

        $erros = [];
        foreach ($grupo as $key => $eti) {
            for ($i=$eti->etiqueta->voltotal; $i >= 1; $i--) {
                $etiquetaEncontrada = $etiquetasAssociadas
                                            ->where('etiqueta.cargaentradaitem', '=', $eti->etiqueta->cargaentradaitem)
                                            ->where('etiqueta.voltotal', '=', $eti->etiqueta->voltotal)
                                            ->where('etiqueta.volnum', '=', $i);
                if ($etiquetaEncontrada->isEmpty())  {
                    $etiFaltando = Etiquetas::where('cargaentradaitem', '=', $eti->etiqueta->cargaentradaitem)->where('volnum', '=', $i)->where('voltotal', '=', $eti->etiqueta->voltotal)->first();
                    $erros[] = [
                        'msg' => 'Faltando etiqueta volume ' . $i . '/' .  $eti->etiqueta->voltotal . ' - Etiqueta ref. ' . $eti->etiqueta->ean13,
                        'etiqueta' => [
                            'ean13' => $etiFaltando->ean13,
                            'volnum' => $etiFaltando->volnum,
                            'voltotal' => $etiFaltando->voltotal,
                        ],
                        'cargaentradaitemid' => $etiFaltando->cargaentradaitem,
                    ];
                }
            }
        }
        $this->attributes['volqtde'] =  $qtdeitens;
        $this->attributes['peso'] =  $peso;
        if (( $erros ? count($erros) : 0 ) > 0) {
          $this->attributes['erroqtde'] = count($erros);
          $this->attributes['erromsg'] =  json_encode($erros);
        } else {
          $this->attributes['erroqtde'] = 0;
          $this->attributes['erromsg'] =  null;
        }



        $conferidoentradaqtde = $this->itens()->where('cargatransferitem.conferidoentrada', '=', 1)->count();
        $percconferido = 0;
        if ($qtdeitens > 0) {
            $percconferido = round(($conferidoentradaqtde / $qtdeitens) * 100, 2);
        }
        $this->attributes['conferidoentradaprogresso'] =  $percconferido;
        $this->attributes['conferidoentradaqtde'] =  $conferidoentradaqtde;
    }

}
