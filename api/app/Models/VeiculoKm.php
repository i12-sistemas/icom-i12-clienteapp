<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VeiculoKm extends Model
{
    protected $table = 'veiculo_km';
    public $timestamps = false;

    protected $dates = ['created_at', 'dhleitura'];

    public function toObject($showComplete = false)
    {
        $dados = $this->toArray();
        $dados['tipo'] = $this->tipo->toObject(true);
        $dados['cidade'] = $this->cidade->toObject(true);
        $dados['created_usuario'] = $this->created_usuario->toObject(false);
        $dados['updated_usuario'] = $this->updated_usuario->toObject(false);
        if ($this->alertamanutid > 0) {
            $dados['alertamanut'] = $this->alertamanut->toObject();
            unset($dados['alertamanutid']);
        }
        unset($dados['tipoid']);
        unset($dados['cidadeid']);
        unset($dados['created_usuarioid']);
        unset($dados['updated_usuarioid']);
        return $dados;
    }

    public function gettableorigemAttribute($value)
    {
      return utf8_encode($value);
    }
    public function settableorigemAttribute($value)
    {
      $this->attributes['tableorigem'] =  utf8_decode($value);
    }

    public function created_usuario()
    {
        return $this->hasOne(Usuario::class, 'id', 'created_usuarioid');
    }

    public function veiculo()
    {
        return $this->hasOne(Veiculo::class, 'id', 'veiculoid');
    }
}
