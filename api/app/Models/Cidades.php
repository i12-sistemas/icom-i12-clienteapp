<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cidades extends Model
{
    protected $table = 'cidades';

    public function toObject($showCompact = false)
    {
        $dados = $this->toArray();
        if ($showCompact) {
            unset($dados['id_uf']);
            unset($dados['created_at']);
            unset($dados['updated_at']);
        } else {
            $dados['created_usuario'] = $this->created_usuario ? $this->created_usuario->toObject(false) : null;
            $dados['updated_usuario'] = $this->updated_usuario ? $this->updated_usuario->toObject(false) : null;
        }
        if ($this->regiao) $dados['regiao'] = $this->regiao->toObject(false);
        unset($dados['regiaoid']);
        unset($dados['created_usuarioid']);
        unset($dados['updated_usuarioid']);
        return $dados;
    }

    public function toSmallObject()
    {
        $dados = [
            'id'    => $this->id,
            'codigo_ibge'    => $this->codigo_ibge,
            'cidade'    => $this->cidade,
            'uf'    => $this->uf,
            'estado'    => $this->estado,
            'ativo'    => $this->ativo
        ];
        if ($this->regiao) $dados['regiao'] = $this->regiao->toSmallObject();
        return $dados;
    }

    public function toSimple()
    {
        $dados = [
            'id'    => $this->id,
            'codigo_ibge'    => $this->codigo_ibge,
            'cidade'    => $this->cidade,
            'uf'    => $this->uf,
            'estado'    => $this->estado,
            'ativo'    => $this->ativo
        ];
        return $dados;
    }

    public function regiao()
    {
        return $this->hasOne(Regiao::class, 'id', 'regiaoid');
    }

    public function getestadoAttribute($value)
    {
      return utf8_encode($value);
    }
    public function setestadoAttribute($value)
    {
      $this->attributes['estado'] =  utf8_decode($value);
    }

    public function getcidadeAttribute($value)
    {
      return utf8_encode($value);
    }
    public function setcidadeAttribute($value)
    {
      $this->attributes['cidade'] =  utf8_decode($value);
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
