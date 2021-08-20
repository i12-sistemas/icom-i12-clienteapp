<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Http\Traits\EtiquetasTrait;

class CargaTransferItem extends Model
{
    use EtiquetasTrait;
    protected $table = 'cargatransferitem';
    public $dates = ['conferidoentradadh'];
    public $timestamps = false;

    public static function boot()
    {
      parent::boot();

      static::deleting(function($model) {
        $log = $model->addLog($model->etiqueta, $model->cargatransfer->useridupdated, 'cargatransferitem', $model->id, 'delete');
        if (!$log) throw new Exception("Nenhum log da etiqueta foi gerado ao excluir");
      });

      static::created(function($model) {
        $log = $model->addLog($model->etiqueta, $model->cargatransfer->useridupdated, 'cargatransferitem', $model->id, 'add');
        if (!$log) throw new Exception("Nenhum log da etiqueta foi gerado ao criar item da carga de transferência");
      });
    }

    public function export()
    {
        $dados = $this->toArray();
        $dados['conferidoentradadh'] = $this->conferidoentradadh ? $this->conferidoentradadh->format('Y-m-d H:i:s') : null;
        if ($this->conferidoentrada === 1) $dados['conferidoentrada_usuario'] = $this->conferidoentrada_usuario ? $this->conferidoentrada_usuario->toSimple() : null;
        $dados['etiqueta'] = $this->etiqueta ? $this->etiqueta->export(true) : null;
        unset($dados['conferidoentradauserid']);
        return $dados;
    }

    public function etiqueta()
    {
        return $this->hasOne(Etiquetas::class, 'ean13', 'etiquetaean13');
    }

    public function cargatransfer()
    {
        return $this->hasOne(CargaTransfer::class, 'id', 'cargatransferid');
    }

    public function conferidoentrada_usuario()
    {
        return $this->hasOne(Usuario::class, 'id', 'conferidoentradauserid');
    }


}
