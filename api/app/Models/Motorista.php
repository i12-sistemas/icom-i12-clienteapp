<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
//new

class Motorista extends Model
{
  protected $table = 'motorista';

  public function toObject($showCompact = true)
  {
    if ($showCompact) {

      $dados = [
        'id'  =>  $this->id,
        'nome'  =>  $this->nome,
        'apelido'  =>  $this->apelido,
        'habilitado'  =>  $this->habilitado,
        'cnhvencimento'  =>  $this->cnhvencimento,
        'moppvencimento'  =>  $this->moppvencimento,
        'ativo'  =>  $this->ativo
      ];
      $dados['veiculo'] = $this->veiculo ? $this->veiculo->toObject(false) : null;
      unset($dados['veiculoid']);

    } else {

      $dados = $this->toArray();
      $dados['cidade'] = $this->cidade ? $this->cidade->toObject(false) : null;
      unset($dados['cidadeid']);
      $dados['veiculo'] = $this->veiculo ? $this->veiculo->toObject(false) : null;
      unset($dados['veiculoid']);
      $dados['created_usuario'] = $this->created_usuario ? $this->created_usuario->toObject(false) : null;
      $dados['updated_usuario'] = $this->updated_usuario ? $this->updated_usuario->toObject(false) : null;
      unset($dados['pwd']);
      unset($dados['created_usuarioid']);
      unset($dados['updated_usuarioid']);

    }
    return $dados;
  }

  public function exportsmall()
  {
    $dados = $this->toArray();
    $dados['created_at'] = $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null;
    $dados['updated_at'] = $this->updated_at ? $this->updated_at->format('Y-m-d H:i:s') : null;
    unset($dados['cidadeid']);
    unset($dados['veiculoid']);

    unset($dados['gerenciamento']);
    unset($dados['gerenciamentooutros']);
    unset($dados['moppvencimento']);
    unset($dados['username']);
    unset($dados['antt']);
    unset($dados['antt']);
    unset($dados['cnhvencimento']);
    unset($dados['salario']);
    unset($dados['cpf']);
    unset($dados['pwd']);
    unset($dados['created_usuarioid']);
    unset($dados['updated_usuarioid']);
    return $dados;
  }

  public function toObjectList()
  {
    $dados = $this->toArray();
    $dados['created_usuario'] = $this->created_usuario ? $this->created_usuario->toSimple(false) : null;
    $dados['updated_usuario'] = $this->updated_usuario ? $this->updated_usuario->toSimple(false) : null;
    $dados['cidade'] = $this->cidade ? $this->cidade->toSimple(false) : null;
    unset($dados['cidadeid']);
    $dados['veiculo'] = $this->veiculo ? $this->veiculo->toObject(false) : null;
    unset($dados['veiculoid']);

    unset($dados['salario']);
    unset($dados['cpf']);
    unset($dados['pwd']);
    unset($dados['created_usuarioid']);
    unset($dados['updated_usuarioid']);
    return $dados;
  }

  public function cidade()
  {
      return $this->hasOne(Cidades::class, 'id', 'cidadeid');
  }

  public function veiculo()
  {
      return $this->hasOne(Veiculo::class, 'id', 'veiculoid')->with('alertamanut', 'cidade', 'tipo');
  }

  public function created_usuario()
  {
      return $this->hasOne(Usuario::class, 'id', 'created_usuarioid');
  }

  public function updated_usuario()
  {
      return $this->hasOne(Usuario::class, 'id', 'updated_usuarioid');
  }

  public function getusernameAttribute($value)
  {
    return utf8_encode($value);
  }
  public function setusernameAttribute($value)
  {
    $this->attributes['username'] =  utf8_decode($value);
  }

  public function getgerenciamentooutrosAttribute($value)
  {
    return utf8_encode($value);
  }
  public function setgerenciamentooutrosAttribute($value)
  {
    if ($this->gerenciamento == 3) {
        $this->attributes['gerenciamentooutros'] =  utf8_decode($value);
    } else {
        $this->attributes['gerenciamentooutros'] =  null;
    }
  }

  public function setgerenciamentoAttribute($value)
  {
    $this->attributes['gerenciamento'] =  $value;

    if (intVal($value) !== 3) {
        $this->attributes['gerenciamentooutros'] =  null;
    }
  }

  public function getanttAttribute($value)
  {
    return utf8_encode($value);
  }
  public function setanttAttribute($value)
  {
    $this->attributes['antt'] =  utf8_decode($value);
  }

  public function getnomeAttribute($value)
  {
    return utf8_encode($value);
  }
  public function setnomeAttribute($value)
  {
    $this->attributes['nome'] =  utf8_decode($value);
  }

  public function getapelidoAttribute($value)
  {
    return utf8_encode($value);
  }
  public function setapelidoAttribute($value)
  {
    $this->attributes['apelido'] =  utf8_decode($value);
  }

  public function getcpfAttribute($value)
  {
    return utf8_encode($value);
  }
  public function setcpfAttribute($value)
  {
    $this->attributes['cpf'] =  utf8_decode($value);
  }

  public function getfoneAttribute($value)
  {
    return utf8_encode($value);
  }
  public function setfoneAttribute($value)
  {
    $this->attributes['fone'] =  utf8_decode($value);
  }
}
