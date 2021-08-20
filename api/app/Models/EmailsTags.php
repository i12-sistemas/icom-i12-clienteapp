<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailsTags extends Model
{
    protected $table = 'emails_tags';
    public $timestamps = false;
    public $incrementing = false;

    public function toObject($showCompact = false)
    {
        $dados = $this->toArray();
        unset($dados['emailid']);
        return $dados;
    }

    public function gettagAttribute($value)
    {
      return utf8_encode($value);
    }
    public function settagAttribute($value)
    {
      $this->attributes['tag'] =  utf8_decode($value);
    }

}
