<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Emails extends Model
{
    protected $table = 'emails';
    public $timestamps = false;

    public function toObject($showCompact = false)
    {
        $dados = $this->toArray();

        $aTags = [];
        foreach ($this->tags as $e) {
            $aTags[] = $e->tag;
        }
        unset($dados['tags']);
        $dados['tags'] = $aTags;

        if(!$showCompact) $dados['clientescount'] = $this->clientes->count();

        return $dados;
    }

    public function clientes()
    {
        return $this->belongsToMany(Cliente::class, 'cliente_email', 'emailid', 'clienteid');
    }



    public function tags()
    {
        return $this->hasMany(EmailsTags::class, 'emailid', 'id');
    }


    public function getemailAttribute($value)
    {
      return utf8_encode($value);
    }
    public function setemailAttribute($value)
    {
      $this->attributes['email'] =  utf8_decode($value);
    }

    public function getnomeAttribute($value)
    {
      return utf8_encode($value);
    }
    public function setnomeAttribute($value)
    {
      $this->attributes['nome'] =  utf8_decode($value);
    }

}
