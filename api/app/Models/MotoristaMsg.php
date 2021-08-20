<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MotoristaMsg extends Model
{
  protected $table = 'motorista_msg';
  protected $dates = ['created_at', 'updated_at', 'deleted_at'];
  protected $hidden = ['deleted_at'];

  public function export($complete = true)
  {
    $dados = $this->toArray();
    $dados['created_at'] = $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null;
    $dados['updated_at'] = $this->updated_at ? $this->updated_at->format('Y-m-d H:i:s') : null;

    $dados['motoristaresp'] = $this->motoristaresp ? $this->motoristaresp->toObject(false) : null;
    unset($dados['idmotoristaresp']);

    $dados['motoristapara'] = $this->motoristapara ? $this->motoristapara->toObject(false) : null;
    unset($dados['paraidmotorista']);

    $dados['created_usuario'] = $this->created_usuario ? $this->created_usuario->toObject(false) : null;
    unset($dados['iduser']);


    return $dados;
  }

  public function motoristaresp()
  {
      return $this->hasOne(Motorista::class, 'id', 'idmotoristaresp');
  }

  public function motoristapara()
  {
      return $this->hasOne(Motorista::class, 'id', 'paraidmotorista');
  }


  public function created_usuario()
  {
    return $this->hasOne(Usuario::class, 'id', 'iduser');
  }



  public function setdenomeAttribute($value)
  {
      $this->attributes['denome'] = utf8_decode($value);
  }
  public function getdenomeAttribute($value)
  {
    return utf8_encode($value);
  }
  public function setdetelefoneAttribute($value)
  {
      $this->attributes['detelefone'] = utf8_decode($value);
  }
  public function getdetelefoneAttribute($value)
  {
    return utf8_encode($value);
  }
  public function settituloAttribute($value)
  {
      $this->attributes['titulo'] = utf8_decode($value);
  }
  public function gettituloAttribute($value)
  {
    return utf8_encode($value);
  }
  public function setmsgAttribute($value)
  {
      $this->attributes['msg'] = utf8_decode($value);
  }
  public function getmsgAttribute($value)
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
