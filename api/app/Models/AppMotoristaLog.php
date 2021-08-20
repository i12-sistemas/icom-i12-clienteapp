<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppMotoristaLog extends Model
{
    protected $table = 'appmotorista_log';
    protected $dates = ['created_at'];
    public $timestamps = false;


    public function export($complete = false)
    {
        $dados = $this->toArray();
        $dados['created_at'] = $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null;
        unset($dados['id']);
        return $dados;
    }

    public function sethostAttribute($value)
    {
        $this->attributes['host'] = utf8_decode($value);
    }
    public function gethostAttribute($value)
    {
      return utf8_encode($value);
    }

    public function seturiAttribute($value)
    {
        $this->attributes['uri'] = utf8_decode($value);
    }
    public function geturiAttribute($value)
    {
      return utf8_encode($value);
    }

    public function setrequestAttribute($value)
    {
        $this->attributes['request'] = utf8_decode($value);
    }
    public function getrequestAttribute($value)
    {
      return utf8_encode($value);
    }


    public function setipAttribute($value)
    {
        $this->attributes['ip'] = utf8_decode($value);
    }
    public function getipAttribute($value)
    {
      return utf8_encode($value);
    }
}
