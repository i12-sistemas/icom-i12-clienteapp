<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CargaEntregaBaixaImg extends Model
{
    protected $table = 'cargaentregabaixaimg';
    protected $dates = ['created_at', 'baixadhlocal'];
    public $timestamps = false;


    public function created_usuario()
    {
        return $this->hasOne(Usuario::class, 'id', 'usuarioid');
    }

    public function motorista()
    {
        return $this->hasOne(Motorista::class, 'id', 'motoritstaid');
    }

    public function cargaentrega()
    {
        return $this->hasOne(CargaEntrega::class, 'id', 'cargaentregaid');
    }


    public function itensdecarga()
    {
        return $this->hasMany(CargaEntregaItem::class, 'ctechave', 'ctechave');
    }

    public function getimgfullnameAttribute($value)
    {
      return utf8_encode($value);
    }
    public function setimgfullnameAttribute($value)
    {
      $this->attributes['imgfullname'] =  utf8_decode($value);
    }

}
