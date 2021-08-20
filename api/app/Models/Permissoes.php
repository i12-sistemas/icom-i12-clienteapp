<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permissoes extends Model
{
    protected $table = 'permissao';
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = 'id';



    public function toNodeTree() {
        $dados = [
            'id' => $this->id,
            'label' => $this->titulo,
            'body' => $this->detalhe,
            // 'selectable' => ($this->grupo == 0),
            // 'expandable' => ($this->grupo == 1),
            // 'tickable' => ($this->grupo == 0),
            'grupo' => $this->grupo
        ];
        return $dados;
    }

    public function gettituloAttribute($value)
    {
      return utf8_encode($value);
    }
    public function settituloAttribute($value)
    {
      $this->attributes['titulo'] =  utf8_decode($value);
    }

    public function getdetalheAttribute($value)
    {
      return utf8_encode($value);
    }
    public function setdetalheAttribute($value)
    {
      $this->attributes['detalhe'] =  utf8_decode($value);
    }
}
