<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
//new
class Veiculo extends Model
{
    protected $table = 'veiculo';

    public function toObject($showComplete = false)
    {
        $dados = $this->toArray();
        $dados['tipo'] = $this->tipo ? $this->tipo->toObject(true) : null;
        $dados['cidade'] = $this->cidade ? $this->cidade->toObject(true) : null;
        $dados['created_usuario'] = $this->created_usuario ? $this->created_usuario->toObject(false) : null;
        $dados['updated_usuario'] = $this->updated_usuario ? $this->updated_usuario->toObject(false) : null;
        if ($this->alertamanutid > 0) {
            if ($this->alertamanut) {
                $dados['alertamanut'] = $this->alertamanut->toObject();
                unset($dados['alertamanutid']);
            }
        }
        $dados['ultimokmdhcheck'] = $this->ultimokmdhcheck ? $this->ultimokmdhcheck : null;
        unset($dados['tipoid']);
        unset($dados['cidadeid']);
        unset($dados['created_usuarioid']);
        unset($dados['updated_usuarioid']);
        return $dados;
    }

    public function exportsmall()
    {
        $dados = $this->toArray();
        $dados['tipo'] = $this->tipo ? $this->tipo->toObject(true) : null;
        // $dados['cidade'] = $this->cidade ? $this->cidade->toObject(true) : null;
        // $dados['created_usuario'] = $this->created_usuario ? $this->created_usuario->toObject(false) : null;
        // $dados['updated_usuario'] = $this->updated_usuario ? $this->updated_usuario->toObject(false) : null;
        // if ($this->alertamanutid > 0) {
        //     if ($this->alertamanut) {
        //         $dados['alertamanut'] = $this->alertamanut->toObject();
        //         unset($dados['alertamanutid']);
        //     }
        // }
        $dados['ultimokmdhcheck'] = $this->ultimokmdhcheck ? $this->ultimokmdhcheck : null;
        unset($dados['tipoid']);
        unset($dados['cidadeid']);
        unset($dados['created_usuarioid']);
        unset($dados['updated_usuarioid']);
        return $dados;
    }


    public function alertamanut()
    {
        return $this->hasOne(VeiculoAlertaManut::class, 'id', 'alertamanutid');
    }

    public function tipo()
    {
        return $this->hasOne(VeiculoTipo::class, 'id', 'tipoid');
    }
    public function cidade()
    {
        return $this->hasOne(Cidades::class, 'id', 'cidadeid')->with('regiao');
    }

    public function getdescricaoAttribute($value)
    {
      return utf8_encode($value);
    }
    public function setdescricaoAttribute($value)
    {
      $this->attributes['descricao'] =  utf8_decode($value);
    }

    public function getplacaAttribute($value)
    {
      return utf8_encode($value);
    }
    public function setplacaAttribute($value)
    {
      $this->attributes['placa'] =  utf8_decode($value);
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
