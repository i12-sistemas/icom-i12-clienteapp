<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EtiquetasLog extends Model
{
    protected $table = 'etiquetas_log';
    protected $dates = ['created_at'];
    public $timestamps = false;

    public function export($complete = true)
    {
        $dados = $this->toArray();
        $dados['created_at'] = $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null;
        if ($complete) {
            $dados['created_usuario'] = $this->created_usuario ? $this->created_usuario->toSimple() : null;
        }
        unset($dados['useridcreated']);

        return $dados;
    }

    public function created_usuario()
    {
        return $this->hasOne(Usuario::class, 'id', 'useridcreated');
    }
}
