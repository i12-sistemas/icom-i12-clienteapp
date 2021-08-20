<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class UsuarioResetPwdTokens extends Model
{
    protected $table = 'usuario_resetpwdtokens';

    protected $dates = ['created_at', 'updated_at'];

    public function usuario()
    {
        return $this->hasOne(Usuario::class, 'id', 'usuarioid');
    }

    public function getexpiradoAttribute($value)
    {
      return ($this->processado != 0) || ($this->expire_at < Carbon::now());
    }

    public function getusernameAttribute($value)
    {
      return utf8_encode($value);
    }
    public function setusernameAttribute($value)
    {
      $this->attributes['username'] =  utf8_decode($value);
    }

    public function getemailAttribute($value)
    {
      return utf8_encode($value);
    }
    public function setemailAttribute($value)
    {
      $this->attributes['email'] =  utf8_decode($value);
    }
}
