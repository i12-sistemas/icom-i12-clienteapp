<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dispositivo extends Model
{
  protected $table = 'dispositivo';
  protected $primaryKey = 'uuid';
  public $incrementing = false;
  protected $dates = ['deleted_at', 'created_at', 'updated_at', 'tokenupdated_at', 'tokenexpire_at'];
  protected $guarded = ['token', 'accesscode', 'tokenexpire_at'];
  protected $fillable = [
    'uuid',
    'platform',
    'version',
    'model',
    'fabricante',
    'descricao',
  ];

  public function export($complete = true)
  {
    $dados = $this->toArray();
    $dados['token'] = $this->token;
    $dados['liberado'] = $this->liberado;
    $dados['tokenexpire_at'] = $this->tokenexpire_at ? $this->tokenexpire_at->format('Y-m-d H:i:s') : null;
    $dados['tokenupdated_at'] = $this->tokenupdated_at ? $this->tokenupdated_at->format('Y-m-d H:i:s') : null;
    $dados['created_at'] = $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null;
    $dados['updated_at'] = $this->updated_at ? $this->updated_at->format('Y-m-d H:i:s') : null;
    $dados['ultimoappmotoristalog'] = $this->ultimoappmotoristalog ? $this->ultimoappmotoristalog->export() : null;
    unset($dados['accesscode']);
    unset($dados['updated_usuarioid']);
    $dados['updated_usuario'] = $this->updated_usuario ? $this->updated_usuario->toObject(false) : null;

    $aItens = [];
    foreach ($this->linksabertos as $e) {
        $aE = $e->export(true);
        $aItens[] = $aE;
    }
    $dados['linksabertos'] = $aItens;

    return $dados;
  }

  public function updated_usuario()
  {
      return $this->hasOne(Usuario::class, 'id', 'updated_usuarioid');
  }

  public function linksabertos()
  {
      return $this->hasMany(DispositivoLink::class, 'uuid', 'uuid')->where('expired', '=', 0)->orderBy('created_at', 'asc');
  }

  public function ultimoappmotoristalog()
  {
      return $this->hasOne(AppMotoristaLog::class, 'uuid', 'uuid')->latest();
  }

  public function appmotoristalog()
  {
      return $this->hasMany(AppMotoristaLog::class, 'uuid', 'uuid')->orderBy('created_at', 'desc');
  }


  public function setuuidAttribute($value)
  {
      $this->attributes['uuid'] = utf8_decode($value);
  }
  public function setplatformAttribute($value)
  {
      $this->attributes['platform'] = utf8_decode($value);
  }

  public function settokenAttribute($value)
  {
      $this->attributes['token'] = utf8_decode($value);
  }

  public function setversionAttribute($value)
  {
      $this->attributes['version'] = utf8_decode($value);
  }
  public function setmodelAttribute($value)
  {
      $this->attributes['model'] = utf8_decode($value);
  }
  public function setfabricanteAttribute($value)
  {
      $this->attributes['fabricante'] = utf8_decode($value);
  }
  public function setdescricaoAttribute($value)
  {
      $this->attributes['descricao'] = utf8_decode($value);
  }

  public function getuuidAttribute($value)
  {
    return utf8_encode($value);
  }
  public function getplatformAttribute($value)
  {
    return utf8_encode($value);
  }
  public function gettokenAttribute($value)
  {
    return utf8_encode($value);
  }
  public function getversionAttribute($value)
  {
    return utf8_encode($value);
  }
  public function getmodelAttribute($value)
  {
    return utf8_encode($value);
  }
  public function getfabricanteAttribute($value)
  {
    return utf8_encode($value);
  }
  public function getdescricaoAttribute($value)
  {
    return utf8_encode($value);
  }

  public function getliberadoAttribute($value)
  {
    $t = isset($this->token) ? $this->token : '';
    return strlen($t) > 10;
  }
}
