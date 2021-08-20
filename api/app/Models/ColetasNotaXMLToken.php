<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ColetasNotaXMLToken extends Model
{
    protected $table = 'coletas_nota_xml_token';
    protected $primaryKey = 'token';
    public $timestamps    = false;
    public $incrementing  = false;
    protected $dates = ['expire_at', 'created_at', 'access_at'];


    public function usuario()
    {
        return $this->hasOne(Usuario::class, 'id', 'usuarioid');
    }


    public function getnotasAttribute($value)
    {
      return utf8_encode($value);
    }
    public function setnotasAttribute($value)
    {
      $this->attributes['notas'] =  utf8_decode($value);
    }

    public function getchaveAttribute($value)
    {
      return utf8_encode($value);
    }
    public function setchaveAttribute($value)
    {
      $this->attributes['chave'] =  utf8_decode($value);
    }

    public function gettoAttribute($value)
    {
      return utf8_encode($value);
    }
    public function settoAttribute($value)
    {
      $this->attributes['to'] =  utf8_decode($value);
    }

    public function getccAttribute($value)
    {
      return utf8_encode($value);
    }
    public function setccAttribute($value)
    {
      $this->attributes['cc'] =  utf8_decode($value);
    }

    public function getassuntoAttribute($value)
    {
      return utf8_encode($value);
    }
    public function setassuntoAttribute($value)
    {
      $this->attributes['assunto'] =  utf8_decode($value);
    }

    public function getmensagemAttribute($value)
    {
      return utf8_encode($value);
    }
    public function setmensagemAttribute($value)
    {
      $this->attributes['mensagem'] =  utf8_decode($value);
    }

    public function getexpiradoAttribute($value)
    {
      return ($this->expire_at < Carbon::now());
    }
}
