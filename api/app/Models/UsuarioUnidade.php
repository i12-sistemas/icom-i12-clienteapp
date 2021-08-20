<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UsuarioUnidade extends Model
{
    protected $table = 'usuario_unidade';
    public $timestamps = false;

    protected $dates = ['created_at'];


    public function created_usuario()
    {
        return $this->hasOne(Usuario::class, 'id', 'created_usuarioid');
    }

    public function usuario()
    {
        return $this->hasOne(Usuario::class, 'id', 'usuarioid');
    }

    public function unidade()
    {
        return $this->hasOne(Unidade::class, 'id', 'unidadeid');
    }
}
