<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UsuarioPerfil extends Model
{
    protected $table = 'usuario_perfil';
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

    public function perfil()
    {
        return $this->hasOne(PerfilAcesso::class, 'id', 'perfilid');
    }
}
