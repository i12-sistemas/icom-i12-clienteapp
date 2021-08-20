<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VeiculoAlertaManut extends Model
{
    protected $table = 'veiculo_alertamanut';
    public $timestamps = false;

    public function toObject()
    {
        $dados = $this->toArray();
        $dados['veiculo'] = [
            'id' => $this->veiculo->id,
            'placa' => $this->veiculo->placa
        ];
        unset($dados['veiculoid']);
        $dados['created_usuario'] = $this->created_usuario->toObject(false);
        if ( $this->revoked_usuario) $dados['revoked_usuario'] = $this->revoked_usuario->toObject(false);
        unset($dados['created_usuarioid']);
        unset($dados['revoked_usuarioid']);
        return $dados;
    }


    public function veiculo()
    {
        return $this->hasOne(Veiculo::class, 'id', 'veiculoid');
    }

    public function getobsAttribute($value)
    {
      return utf8_encode($value);
    }
    public function setobsAttribute($value)
    {
      $this->attributes['obs'] =  utf8_decode($value);
    }

    public function created_usuario()
    {
        return $this->hasOne(Usuario::class, 'id', 'created_usuarioid');
    }

    public function revoked_usuario()
    {
        return $this->hasOne(Usuario::class, 'id', 'revoked_usuarioid');
    }
}
