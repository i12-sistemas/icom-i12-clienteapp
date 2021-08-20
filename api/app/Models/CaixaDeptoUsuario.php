<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CaixaDeptoUsuario extends Model
{
    protected $table = 'caixa_depto_usuario';
    protected $dates = ['created_at'];
    public $timestamps = false;

    public function export($complete = true)
    {
        $dados = $this->toArray();
        $dados['created_at'] = $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null;
        $dados['usuario'] = $this->usuario ? $this->usuario->toObject(false) : null;
        $dados['created_usuario'] = $this->created_usuario ? $this->created_usuario->toObject(false) : null;
        unset($dados['created_usuarioid']);
        unset($dados['caixadeptoid']);
        unset($dados['usuarioid']);
        return $dados;
    }

    public function created_usuario()
    {
        return $this->hasOne(Usuario::class, 'id', 'created_usuarioid');
    }

    public function usuario()
    {
        return $this->hasOne(Usuario::class, 'id', 'usuarioid');
    }
}
