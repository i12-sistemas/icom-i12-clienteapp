<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ColetasItens extends Model
{
    protected $table = 'coletas_itens';


    public function toObject($showCompact = false)
    {
        $dados = $this->toArray();
        unset($dados['coletaid']);

        if ($this->produtoid > 0) {
            $dados['produto'] = $this->produto->toObject(True);
        } else {
            $dados['produto'] = [
                'nome' => $this->produtodescricao,
                'ativo' => true
            ];
        }
        unset($dados['produtodescricao']);
        unset($dados['produtoid']);
        unset($dados['gestaocliente_id']);

        if (!$showCompact) {
            $dados['created_usuario'] = $this->created_usuario->toObject(false);
            $dados['updated_usuario'] = $this->updated_usuario->toObject(false);
        }
        unset($dados['created_usuarioid']);

        unset($dados['updated_usuarioid']);

        return $dados;
    }

    public function produto()
    {
        return $this->hasOne(Produto::class, 'id', 'produtoid');
    }

    public function created_usuario()
    {
        return $this->hasOne(Usuario::class, 'id', 'created_usuarioid');
    }

    public function updated_usuario()
    {
        return $this->hasOne(Usuario::class, 'id', 'updated_usuarioid');
    }

    public function getprodutodescricaoAttribute($value)
    {
      return utf8_encode($value);
    }
    public function setprodutodescricaoAttribute($value)
    {
      $this->attributes['produtodescricao'] =  utf8_decode($value);
    }


    public function getembalagemAttribute($value)
    {
      return utf8_encode($value);
    }
    public function setembalagemAttribute($value)
    {
      $this->attributes['embalagem'] =  utf8_decode($value);
    }
}
