<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcertoViagemRoteiro extends Model
{
    protected $table = 'acertoviagemroteiro';
    public $timestamps = false;

    public function export($complete = true)
    {
        $dados = $this->toArray();
        $dados['cidade'] = $this->cidade ? $this->cidade->toSmallObject() : null;
        unset($dados['acertoid']);
        unset($dados['cidadeid']);
        return $dados;
    }

    public function cidade()
    {
        return $this->hasOne(Cidades::class, 'id', 'cidadeid');
    }

    public function getrotaAttribute($value)
    {
      return utf8_encode($value);
    }
    public function setrotaAttribute($value)
    {
      $this->attributes['rota'] =  utf8_decode($value);
    }
}
