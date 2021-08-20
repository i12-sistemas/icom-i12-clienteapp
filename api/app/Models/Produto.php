<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Produto extends Model
{
  protected $table = 'produto';

  public function toObject($showCompact = true)
  {
      if ($showCompact) {
        $dados = [
            'id' => $this->id,
            'nome' => $this->nome,
            'onu' => $this->onu,
            'ativo' => $this->ativo
        ];
      } else {
        $dados = $this->toArray();
        $dados['created_usuario'] = $this->created_usuario->toObject(false);
        $dados['updated_usuario'] = $this->updated_usuario->toObject(false);
        unset($dados['created_usuarioid']);
        unset($dados['updated_usuarioid']);
      }
      return $dados;
  }

  public function created_usuario()
  {
      return $this->hasOne(Usuario::class, 'id', 'created_usuarioid');
  }

  public function updated_usuario()
  {
      return $this->hasOne(Usuario::class, 'id', 'updated_usuarioid');
  }

  public function getnomeAttribute($value)
  {
    return utf8_encode($value);
  }
  public function setnomeAttribute($value)
  {
    $this->attributes['nome'] =  utf8_decode($value);
  }

  public function getclasseriscoAttribute($value)
  {
    return utf8_encode($value);
  }
  public function setclasseriscoAttribute($value)
  {
    $this->attributes['classerisco'] =  utf8_decode($value);
  }


  public function getriscosubsAttribute($value)
  {
    return utf8_encode($value);
  }
  public function setriscosubsAttribute($value)
  {
    $this->attributes['riscosubs'] =  utf8_decode($value);
  }

  public function getriscosubs2Attribute($value)
  {
    return utf8_encode($value);
  }
  public function setriscosubs2Attribute($value)
  {
    $this->attributes['riscosubs2'] =  utf8_decode($value);
  }


  public function getnumriscoAttribute($value)
  {
    return utf8_encode($value);
  }
  public function setnumriscoAttribute($value)
  {
    $this->attributes['numrisco'] =  utf8_decode($value);
  }

  public function getgrupoembAttribute($value)
  {
    return utf8_encode($value);
  }
  public function setgrupoembAttribute($value)
  {
    $this->attributes['grupoemb'] =  utf8_decode($value);
  }


  public function getprovespecAttribute($value)
  {
    return utf8_encode($value);
  }
  public function setprovespecAttribute($value)
  {
    $this->attributes['provespec'] =  utf8_decode($value);
  }

  public function getqtdeltdavAttribute($value)
  {
    return utf8_encode($value);
  }
  public function setqtdeltdavAttribute($value)
  {
    $this->attributes['qtdeltdav'] =  utf8_decode($value);
  }

  public function getqtdeltdaeAttribute($value)
  {
    return utf8_encode($value);
  }
  public function setqtdeltdaeAttribute($value)
  {
    $this->attributes['qtdeltdae'] =  utf8_decode($value);
  }

  public function getembibcinstAttribute($value)
  {
    return utf8_encode($value);
  }
  public function setembibcinstAttribute($value)
  {
    $this->attributes['embibcinst'] =  utf8_decode($value);
  }

  public function getembibcprovAttribute($value)
  {
    return utf8_encode($value);
  }
  public function setembibcprovAttribute($value)
  {
    $this->attributes['embibcprov'] =  utf8_decode($value);
  }

  public function gettanqueinstAttribute($value)
  {
    return utf8_encode($value);
  }
  public function settanqueinstAttribute($value)
  {
    $this->attributes['tanqueinst'] =  utf8_decode($value);
  }

  public function gettanqueprovAttribute($value)
  {
    return utf8_encode($value);
  }
  public function settanqueprovAttribute($value)
  {
    $this->attributes['tanqueprov'] =  utf8_decode($value);
  }

  public function getpolimerizaAttribute($value)
  {
    return utf8_encode($value);
  }
  public function setpolimerizaAttribute($value)
  {
    $this->attributes['polimeriza'] =  utf8_decode($value);
  }

  public function getepiAttribute($value)
  {
    return utf8_encode($value);
  }
  public function setepiAttribute($value)
  {
    $this->attributes['epi'] =  utf8_decode($value);
  }

  public function getkitAttribute($value)
  {
    return utf8_encode($value);
  }
  public function setkitAttribute($value)
  {
    $this->attributes['kit'] =  utf8_decode($value);
  }

}
