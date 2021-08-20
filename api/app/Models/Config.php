<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Exception;
use Carbon\Carbon;

class Config extends Model
{
  protected $table      = 'config';
  protected $primaryKey = 'id';
  public $timestamps    = false;
  protected $hidden     = ['texto', 'arquivo', 'ext'];
  public $incrementing  = false;

  public function export()
    {
        $dados = [
            'id'    => $this->id,
            'tipo'    => $this->tipo,
            'valor'    => $this->asValue(),
        ];
        return $dados;
    }

  // default used if not found id
  public function asValue($default = null)
  {
    // if (!$this->id) return $default;

    if ($this->tipo == 'string') {
      return ($this->valor ? (string)$this->valor : '');
    } else if ($this->tipo == 'double') {
      try {
        return number_format($this->valor, 10, ".","");
      } catch (\Throwable $th) {
        return $default;
      }
    } else if ($this->tipo == 'integer') {
      try {
        return intval($this->valor);
      } catch (\Throwable $th) {
        return $default;
      }
    } else if ($this->tipo == 'time') {
      try {
        $t = Carbon::createFromFormat('H:i', $this->valor);
        return $t->format('H:i');
      } catch (\Throwable $th) {
        return $default;
      }
    } else if ($this->tipo == 'mediumtext') {
        try {
            return $this->texto;
        } catch (\Throwable $th) {
            return $default;
        }
    } else if ($this->tipo == 'json') {
        try {
            return json_decode($this->texto);
        } catch (\Throwable $th) {
            return $default;
        }
    } else {
      return $default;
    }
  }

  public function getidAttribute($value)
  {
    return utf8_encode($value);
  }
  public function setidAttribute($value)
  {
    $this->attributes['id'] =  utf8_decode($value);
  }

  // tipo
  public function gettipoAttribute($value)
  {
    return utf8_encode($value);
  }
  public function settipoAttribute($value)
  {
    $this->attributes['tipo'] =  utf8_decode($value);
  }


  // valor
  public function getvalorAttribute($value)
  {
    return utf8_encode($value);
  }
  public function setvalorAttribute($value)
  {
    $this->attributes['valor'] =  utf8_decode($value);
  }

  // texto
  public function gettextoAttribute($value)
  {
    return utf8_encode($value);
  }
  public function settextoAttribute($value)
  {
    $this->attributes['texto'] =  utf8_decode($value);
  }

  // texto
  public function getextAttribute($value)
  {
    return utf8_encode($value);
  }
  public function setextAttribute($value)
  {
    $this->attributes['ext'] =  utf8_decode($value);
  }
}
