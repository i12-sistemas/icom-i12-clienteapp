<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VeiculoTipo extends Model
{
    protected $table = 'veiculo_tipo';

    public function toObject($showCompact = false)
    {
        $dados = $this->toArray();
        if (!$showCompact) {
            $dados['veiculoscount'] = $this->veiculos()->count();
            $dados['created_usuario'] = $this->created_usuario->toObject(false);
            $dados['updated_usuario'] = $this->updated_usuario->toObject(false);
        }
        unset($dados['created_usuarioid']);
        unset($dados['updated_usuarioid']);
        return $dados;
    }

    public function veiculos()
    {
        return $this->hasMany(Veiculo::class, 'tipoid', 'id');
    }


    public function gettipoAttribute($value)
    {
      return utf8_encode($value);
    }
    public function settipoAttribute($value)
    {
      $this->attributes['tipo'] =  utf8_decode($value);
    }

    public function created_usuario()
    {
        return $this->hasOne(Usuario::class, 'id', 'created_usuarioid');
    }

    public function updated_usuario()
    {
        return $this->hasOne(Usuario::class, 'id', 'updated_usuarioid');
    }
}
