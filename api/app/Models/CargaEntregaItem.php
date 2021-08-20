<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Http\Traits\EtiquetasTrait;

class CargaEntregaItem extends Model
{
    use EtiquetasTrait;
    protected $table = 'cargaentregaitem';
    protected $dates = ['entreguedh'];
    public $timestamps = false;


    public static function boot()
    {
      parent::boot();

      static::deleting(function($model) {
        $log = $model->addLog($model->etiqueta, $model->cargaentrega->useridupdated, 'cargaentregaitem', $model->id, 'delete');
        if (!$log) throw new Exception("Nenhum log da etiqueta foi gerado ao excluir");
      });

      static::created(function($model) {
        $log = $model->addLog($model->etiqueta, $model->cargaentrega->useridupdated, 'cargaentregaitem', $model->id, 'add');
        if (!$log) throw new Exception("Nenhum log da etiqueta foi gerado ao criar item da carga de entrega");
      });
    }



    public function export($complete = true)
    {
        $dados = $this->toArray();
        $dados['etiqueta'] = $this->etiqueta ? $this->etiqueta->export(true) : null;
        return $dados;
    }

    public function etiqueta()
    {
        return $this->hasOne(Etiquetas::class, 'ean13', 'etiquetaean13');
    }

    public function cargaentrega()
    {
        return $this->hasOne(CargaEntrega::class, 'id', 'cargaentregaid');
    }
}
