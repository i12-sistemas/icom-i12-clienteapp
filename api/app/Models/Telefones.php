<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Telefones extends Model
{
    protected $table = 'telefones';

    public function export($complete = true)
    {
        $dados = $this->toArray();
        $dados['created_at'] = $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null;
        $dados['updated_at'] = $this->updated_at ? $this->updated_at->format('Y-m-d H:i:s') : null;
        unset($dados['created_usuarioid']);
        unset($dados['updated_usuarioid']);
        if ($complete) {
            $dados['created_usuario'] = $this->created_usuario ? $this->created_usuario->toObject(false) : null;
            $dados['updated_usuario'] = $this->updated_usuario ? $this->updated_usuario->toObject(false) : null;
        }
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

    public function gettelefoneAttribute($value)
    {
      return utf8_encode($value);
    }
    public function settelefoneAttribute($value)
    {
      $this->attributes['telefone'] =  utf8_decode($value);
    }


    public function getcontatoAttribute($value)
    {
      return utf8_encode($value);
    }
    public function setcontatoAttribute($value)
    {
      $this->attributes['contato'] =  utf8_decode($value);
    }

    public function getcategAttribute($value)
    {
      return utf8_encode($value);
    }
    public function setcategAttribute($value)
    {
      $this->attributes['categ'] =  utf8_decode($value);
    }

    public function geticonAttribute($value)
    {
      return utf8_encode($value);
    }
    public function seticonAttribute($value)
    {
      $this->attributes['icon'] =  utf8_decode($value);
    }
}
