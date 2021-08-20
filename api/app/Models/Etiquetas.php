<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Cliente;
use App\Http\Traits\EtiquetasTrait;

class Etiquetas extends Model
{
    use EtiquetasTrait;
    protected $table = 'etiquetas';
    protected $primaryKey = 'ean13';
    public $incrementing = false;
    protected $dates = ['dataref', 'created_at', 'updated_at', 'conferidoentradadh'];
    public $timestamps = true;

    public static function boot()
    {
      parent::boot();

      static::deleting(function($model) {
        $log = $model->addLog($model, $model->useridupdated, 'cargaentradaitem', $model->cargaentradaitem, 'delete');
        if (!$log) throw new Exception("Nenhum log da etiqueta foi gerado ao excluir");
      });

      static::created(function($model) {
        $log = $model->addLog($model, $model->useridupdated, 'cargaentradaitem', $model->cargaentradaitem, 'add');
        if (!$log) throw new Exception("Nenhum log da etiqueta foi gerado ao criar etiqueta");
      });

      self::creating(function ($model) {
        $tentativa = 1;
        $continue = true;
        while ($continue) {
            $numero = rand(1,99999);
            $finded = Etiquetas::whereRaw('date(dataref)=?',  [$model->dataref->format('Y-m-d')])
                              ->where('numero', '=', $numero)
                              ->first();
            if (!$finded) {
                $model->numero = $numero;
                $ean13 = '2' . $model->dataref->format('ymd') . str_pad($numero, 5, "0", STR_PAD_LEFT);
                $dv = generateEANdigit($ean13);
                $model->ean13 = $ean13 . $dv;
                $continue = false;
            }
            $tentativa++;
        }
      });


    }

    public function getpesovolAttribute($value)
    {
        $peso = 0;
        if ($this->voltotal === 1) $peso = $this->pesototal;
        if ($this->voltotal > 1) {
            $peso = ($this->pesototal / $this->voltotal);
        }
      return $peso;
    }

    public function export($complete = true)
    {
        $dados = $this->toArray();
        $dados['dataref'] = $this->dataref ? $this->dataref->format('Y-m-d') : null;
        $dados['created_at'] = $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null;
        $dados['updated_at'] = $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null;
        $dados['conferidoentradadh'] = $this->conferidoentradadh ? $this->conferidoentradadh->format('Y-m-d H:i:s') : null;
        if ($this->conferidoentrada === 1) $dados['conferidoentrada_usuario'] = $this->conferidoentrada_usuario ? $this->conferidoentrada_usuario->toSimple() : null;
        if ($complete) {
            $dados['created_usuario'] = $this->created_usuario ? $this->created_usuario->toSimple() : null;
            $dados['updated_usuario'] = $this->updated_usuario ? $this->updated_usuario->toSimple() : null;
            $dados['cargaentradaitem'] = $this->itemcargaentrada ? $this->itemcargaentrada->export(true, true) : null;
            $dados['cargaentrada'] = $this->cargaentrada ? $this->cargaentrada->toSimple() : null;
            $dados['unidadeatual'] = $this->unidadeatual ? $this->unidadeatual->toSimple() : null;
        }


        unset($dados['conferidoentradauserid']);
        unset($dados['unidadeatualid']);
        unset($dados['etiquetasfilhas']);
        unset($dados['useridcreated']);
        unset($dados['useridupdated']);

        if (($this->volnum === 1) && ($this->voltotal > 1)) {
            $aEtiquetas = [];
            foreach ($this->etiquetasfilhas as $e) {
                $aEtiquetas[] = $e->export(false);
            }
            $dados['etiquetasfilhas'] = $aEtiquetas;
        }

        if ($complete) {
            $dados['palete'] = $this->palete ? $this->palete->toSimple() : null;

            $origem = $this->origem();
            $dados['origem'] = $origem ? $origem->toSmallObject() : null;

            $destinatario = $this->destinatario();
            $dados['destinatario'] = $destinatario ? $destinatario->toSmallObject() : null;

            if ($this->itemcargaentrada)  {
                $coletanota = $this->itemcargaentrada->coletanota;
                $dados['coletanota'] = $coletanota ? $coletanota->export() : null;
            }

            if ($this->volnum > 1) {
                $etiquetapai = $this->etiquetapai;
                $dados['ean13pai'] = $etiquetapai ? $etiquetapai->ean13 : null;
                unset($dados['etiquetapai']);
            }

            $aLogs = [];
            foreach ($this->logs as $key =>$log) {
                $a = $log->export(true);
                $a['nordem'] = $this->logs->count()-$key;
                $aLogs[] = $a;
            }
            $dados['logs'] = $aLogs;

        }
        $dados['ultimolog'] = $this->ultimolog ? $this->ultimolog->export(false) : null;
        unset($dados['logatualid']);
        unset($dados['paleteid']);

        unset($dados['itemcargaentrada']);

        return $dados;
    }

    public function etiquetasfilhas()
    {
        return $this->hasMany(Etiquetas::class, 'cargaentradaitem', 'cargaentradaitem')
                    ->where('volnum', '>', 1)
                    ->where('voltotal', '>', 1)
                    ->orderby('volnum', 'ASC');
    }

    public function logs()
    {
        return $this->hasMany(EtiquetasLog::class, 'ean13', 'ean13')
                    ->orderby('created_at', 'DESC');
    }

    public function ultimolog()
    {
        return $this->hasOne(EtiquetasLog::class, 'id', 'logatualid');
    }

    public function etiquetapai()
    {
        return $this->hasOne(Etiquetas::class, 'cargaentradaitem', 'cargaentradaitem')
                    ->where('volnum', '=', 1)
                    ->where('voltotal', '>', 1)
                    ->orderby('volnum', 'ASC');
    }


    public function itemcargaentrada()
    {
        return $this->hasOne(CargaEntradaItem::class, 'id', 'cargaentradaitem')->with('manual_usuario');
    }


    public function unidadeatual()
    {
        return $this->hasOne(Unidade::class, 'id', 'unidadeatualid')->with('cidade');
    }

    public function palete()
    {
        return $this->hasOne(Paletes::class, 'id', 'paleteid');
    }

    public function cargaentrada()
    {
        return $this->hasOneThrough(
            CargaEntrada::class,
            CargaEntradaItem::class,
            'id', // Foreign key on the cars table...
            'id', // Foreign key on the owners table...
            'cargaentradaitem', // Local key on the mechanics table...
            'cargaentradaid' // Local key on the cars table...
        )->with('unidadeentrada');
    }

    public function destinatario()
    {
        $cnpj = '';
        if ($this->itemcargaentrada->coletanota) {
            if ($this->itemcargaentrada->coletanota->destinatariocnpj !== '') $cnpj = $this->itemcargaentrada->coletanota->destinatariocnpj;
        }
        if ($cnpj === '') {
            if ($this->itemcargaentrada->coletaid > 0) {
                $cnpj = $this->itemcargaentrada->coleta->clientedestino->cnpj;
            }
        }
        if ($cnpj == '') $cnpj = null;
        return Cliente::where('cnpj', '=', $cnpj)->first();
    }

    public function origem()
    {
        $cnpj = '';
        if ($this->itemcargaentrada->coletanota) {
            if ($this->itemcargaentrada->coletanota->remetentecnpj !== '') $cnpj = $this->itemcargaentrada->coletanota->remetentecnpj;
        }
        if ($cnpj === '') {
            if ($this->itemcargaentrada->coletaid > 0) {
                $cnpj = $this->itemcargaentrada->coleta->clienteorigem->cnpj;
            }
        }
        if ($cnpj == '') $cnpj = null;
        return Cliente::where('cnpj', '=', $cnpj)->first();
    }

    public function created_usuario()
    {
        return $this->hasOne(Usuario::class, 'id', 'useridcreated');
    }

    public function conferidoentrada_usuario()
    {
        return $this->hasOne(Usuario::class, 'id', 'conferidoentradauserid');
    }

    public function updated_usuario()
    {
        return $this->hasOne(Usuario::class, 'id', 'useridupdated');
    }

    public function barcode()
    {
        // code, $type, $w = 2, $h = 30, $color = array(0, 0, 0), $showCode = false)
        return  \DNS1D::getBarcodePNG($this->ean13, 'EAN13');
        // return  $barcode = \DNS1D::getBarcodeHTML($this->ean13, 'EAN13', 1, 40, 'black', true);
    }

    public function barcodenfe()
    {
        // code, $type, $w = 2, $h = 30, $color = array(0, 0, 0), $showCode = false)
        return  \DNS1D::getBarcodePNG($this->itemcargaentrada->nfechave, 'C128C');
        // return  $barcode = \DNS1D::getBarcodeHTML($this->ean13, 'EAN13', 1, 40, 'black', true);
    }
}
