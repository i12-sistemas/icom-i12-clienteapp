<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ClienteUsuarioResetPwdTokens extends Model
{
    protected $table = 'clienteusuarioresetpwdtokens';

    protected $dates = ['created_at', 'updated_at'];

    public function clienteusuario()
    {
        return $this->hasOne(ClienteUsuario::class, 'id', 'clienteusuarioid');
    }

    public function getexpiradoAttribute($value)
    {
      return ($this->processado != 0) || ($this->expire_at < Carbon::now());
    }

    public function getcelularAttribute($value)
    {
      return utf8_encode($value);
    }
    public function setcelularAttribute($value)
    {
      $this->attributes['celular'] =  utf8_decode($value);
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
