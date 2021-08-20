<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Unidade extends Model
{
    protected $table = 'unidade';

    public function toObject($showComplete = false)
    {
        $dados = $this->toArray();
        $dados['cidade'] = $this->cidade ? $this->cidade->toSmallObject() : null;
        unset($dados['cidadeid']);

        if ($showComplete) {
            if ($this->created_usuario) {
                $dados['created_usuario'] = [
                    'id' => $this->created_usuario->id,
                    'nome' => $this->created_usuario->nome,
                    'ativo' => $this->created_usuario->ativo
                ];
            }
            if ($this->updated_usuario) {
                $dados['updated_usuario'] = [
                    'id' => $this->updated_usuario->id,
                    'nome' => $this->updated_usuario->nome,
                    'ativo' => $this->updated_usuario->ativo
                ];
            }
            unset($dados['created_usuarioid']);
            unset($dados['updated_usuarioid']);
        }

        return $dados;
    }

    public function toSimple()
    {
        $dados = [
            'id' => $this->id,
            'razaosocial' => $this->razaosocial,
            'fantasia' => $this->fantasia,
            'cnpj' => $this->cnpj,
            'logradouro' => $this->logradouro,
            'endereco' => $this->endereco,
            'numero' => $this->enderenumeroco,
            'bairro' => $this->bairro,
            'cep' => $this->cep,
            'complemento' => $this->complemento,
            'ativo' => $this->ativo,
            'cidade' => $this->cidade ? $this->cidade->toSimple() : null
        ];
        return $dados;
    }


    public function created_usuario()
    {
        return $this->hasOne(Usuario::class, 'id', 'created_usuarioid');
    }

    public function updated_usuario()
    {
        return $this->hasOne(Usuario::class, 'id', 'updated_usuarioid');
    }

    public function cidade()
    {
        return $this->hasOne(Cidades::class, 'id', 'cidadeid');
    }

    public function getrazaosocialAttribute($value)
    {
      return utf8_encode($value);
    }
    public function setrazaosocialAttribute($value)
    {
      $this->attributes['razaosocial'] =  utf8_decode($value);
    }

    public function getfantasiaAttribute($value)
    {
      return utf8_encode($value);
    }
    public function setfantasiaAttribute($value)
    {
      $this->attributes['fantasia'] =  utf8_decode($value);
    }

    public function getlogradouroAttribute($value)
    {
      return utf8_encode($value);
    }
    public function setlogradouroAttribute($value)
    {
      $this->attributes['logradouro'] =  utf8_decode($value);
    }

    public function getenderecoAttribute($value)
    {
      return utf8_encode($value);
    }
    public function setenderecoAttribute($value)
    {
      $this->attributes['endereco'] =  utf8_decode($value);
    }

    public function getbairroAttribute($value)
    {
      return utf8_encode($value);
    }
    public function setbairroAttribute($value)
    {
      $this->attributes['bairro'] =  utf8_decode($value);
    }

    public function getcomplementoAttribute($value)
    {
      return utf8_encode($value);
    }
    public function setcomplementoAttribute($value)
    {
      $this->attributes['complemento'] =  utf8_decode($value);
    }
}
