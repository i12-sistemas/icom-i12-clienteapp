<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use App\Models\Etiquetas;

class Paletes extends Model
{

    protected $table = 'paletes';
    public $timestamps = true;

    public function export($complete = true)
    {
        $dados = $this->toArray();
        $dados['created_at'] = $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null;
        $dados['updated_at'] = $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null;
        if ($complete) {
            $dados['created_usuario'] = $this->created_usuario ? $this->created_usuario->toObject(false) : null;
            $dados['updated_usuario'] = $this->updated_usuario ? $this->updated_usuario->toObject(false) : null;
        }

        $dados['unidade'] = $this->unidade ? $this->unidade->toSimple() : null;
        unset($dados['unidadeid']);
        unset($dados['useridcreated']);
        unset($dados['useridupdated']);

        if ($complete) {
            $aItens = [];
            foreach ($this->itens as $key =>$item) {
                $a = $item->export($complete);
                $a['nordem'] = $this->itens->count()-$key;
                $aItens[] = $a;
            }
            $dados['itens'] = $aItens;
        }
        return $dados;
    }

    public function toSimple()
    {
        $dados = $this->toArray();
        $dados['created_at'] = $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null;
        $dados['updated_at'] = $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null;
        $dados['unidade'] = $this->unidade ? $this->unidade->toSimple() : null;
        unset($dados['unidadeid']);
        unset($dados['useridcreated']);
        unset($dados['useridupdated']);
        return $dados;
    }


    public static function boot()
    {
      parent::boot();

      self::creating(function ($model) {
        $tentativa = 1;
        $continue = true;
        while ($continue) {
            $numero = rand(1,99999);
            $ean13 = '3' . Carbon::now()->format('ymd') . str_pad($numero, 5, "0", STR_PAD_LEFT);
            $dv = generateEANdigit($ean13);
            $ean13 = $ean13 . $dv;
            $finded = Paletes::where('ean13', '=', $ean13)->first();
            if (!$finded) {
              $model->ean13 = $ean13;
              $continue = false;
            }
            $tentativa++;
        }
      });
    }

    public function totaliza()
    {
        $volqtde = 0;
        $peso = 0;

        foreach ($this->itens as $item) {
            $volqtde += 1;
            $peso += $item->etiqueta->pesovol;
        }


        $etiquetasAssociadas = $this->itens()->with('etiqueta')->get();
        $grupo = $this->itens()->with('etiqueta')
                        ->groupBy('etiquetas.cargaentradaitem')
                        ->groupBy('etiquetas.voltotal')
                        ->orderBy('etiquetas.cargaentradaitem')
                        ->orderBy('etiquetas.volnum')
                        ->orderBy('etiquetas.voltotal')
                        ->get();

        $erros = [];
        // if ($etiquetasAssociadas) {
        //   foreach ($grupo as $key => $eti) {
        //     for ($i=$eti->voltotal; $i >= 1; $i--) {
        //       $volqtde += 1;
        //       $etiquetaEncontrada = $etiquetasAssociadas
        //                             ->where('cargaentradaitem', '=', $eti->cargaentradaitem)
        //                             ->where('voltotal', '=', $eti->voltotal)
        //                             ->where('volnum', '=', $i);
        //       if ($etiquetaEncontrada->isEmpty())  {
        //         $etiFaltando = Etiquetas::where('cargaentradaitem', '=', $eti->cargaentradaitem)->where('volnum', '=', $i)->where('voltotal', '=', $eti->voltotal)->first();
        //         $erros[] = [
        //           'msg' => 'Faltando etiqueta volume ' . $i . '/' .  $eti->voltotal . ' - Etiqueta ref. ' . $eti->ean13,
        //           'cargaentradaitemid' => $etiFaltando->cargaentradaitem,
        //           'etiqueta' => [
        //             'ean13' => $etiFaltando->ean13,
        //             'volnum' => $etiFaltando->volnum,
        //             'voltotal' => $etiFaltando->voltotal,
        //           ],
        //         ];
        //       }
        //     }
        //   }
        // }
        if ($etiquetasAssociadas ? $etiquetasAssociadas->count() == 0 : true) {
          $erros[] = [
            'msg' => 'Nenhum item informado',
          ];
        }
        $this->attributes['volqtde'] =  $volqtde;
        $this->attributes['pesototal'] =  $peso ? $peso : 0;
        $this->attributes['erroqtde'] =  $erros ? count($erros) : 0;
        $this->attributes['erromsg'] =  count($erros) > 0 ? json_encode($erros) : null;

    }

    public function barcode()
    {
        // code, $type, $w = 2, $h = 30, $color = array(0, 0, 0), $showCode = false)
        return  \DNS1D::getBarcodePNG($this->ean13, 'EAN13');
        // return  $barcode = \DNS1D::getBarcodeHTML($this->ean13, 'EAN13', 1, 40, 'black', true);
    }

    public function itens()
    {
        return $this->hasMany(PaletesItem::class, 'paleteid', 'id')
                        ->join('etiquetas', 'paletesitem.ean13', '=', 'etiquetas.ean13')
                        ->orderBy('etiquetas.cargaentradaitem')
                        ->orderBy('etiquetas.volnum')
                        ->orderBy('etiquetas.voltotal')
                        ->with('etiqueta');
    }

    public function itensEtiquetaDetalhe()
    {
        return $this->hasMany(PaletesItem::class, 'paleteid', 'id')
                        ->select(\DB::raw('group_concat(distinct etiquetas.volnum order by etiquetas.volnum separator ", ") as volumes'), \DB::raw('count(distinct etiquetas.volnum) as totalvolume'), 'paletesitem.*')
                        ->join('etiquetas', 'paletesitem.ean13', '=', 'etiquetas.ean13')
                        ->join('cargaentradaitem', 'etiquetas.cargaentradaitem', '=', 'cargaentradaitem.id')
                        ->join('coletas_nota', 'cargaentradaitem.coletanotaid', '=', 'coletas_nota.id')
                        ->orderBy('coletas_nota.notanumero')
                        ->orderBy('coletas_nota.notaserie')
                        ->orderBy('etiquetas.cargaentradaitem')
                        ->orderBy('etiquetas.volnum')
                        ->orderBy('etiquetas.voltotal')
                        ->groupBy('coletas_nota.notachave')
                        ->with('etiqueta');
    }


    public function unidade()
    {
        return $this->hasOne(Unidade::class, 'id', 'unidadeid');
    }

    public function created_usuario()
    {
        return $this->hasOne(Usuario::class, 'id', 'useridcreated');
    }

    public function updated_usuario()
    {
        return $this->hasOne(Usuario::class, 'id', 'useridupdated');
    }


    public function getdescricaoAttribute($value)
    {
      return utf8_encode($value);
    }
    public function setdescricaoAttribute($value)
    {
      $this->attributes['descricao'] =  utf8_decode($value);
    }

}
