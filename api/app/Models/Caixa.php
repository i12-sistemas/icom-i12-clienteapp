<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Caixa extends Model
{
    protected $table = 'caixa';
    protected $dates = ['created_at'];
    public $timestamps = false;

    public function export($complete = true)
    {
        $dados = $this->toArray();
        $dados['created_at'] = $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null;
        $dados['categoria'] = $this->categoria ? $this->categoria->export(false) : null;
        $dados['depto'] = $this->depto ? $this->depto->export(false) : null;
        unset($dados['deptoid']);
        unset($dados['categoriaid']);
        unset($dados['created_usuarioid']);
        if ($complete) {
            $dados['created_usuario'] = $this->created_usuario ? $this->created_usuario->toObject(false) : null;
        }


        return $dados;
    }

    public function created_usuario()
    {
        return $this->hasOne(Usuario::class, 'id', 'created_usuarioid');
    }

    public function categoria()
    {
        return $this->hasOne(CaixaCategoria::class, 'id', 'categoriaid');
    }

    public function depto()
    {
        return $this->hasOne(CaixaDepto::class, 'id', 'deptoid');
    }

    public function gethistoricoAttribute($value)
    {
      return utf8_encode($value);
    }
    public function sethistoricoAttribute($value)
    {
      $this->attributes['historico'] =  utf8_decode($value);
    }
}
