<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DispositivoLink extends Model
{
    use SoftDeletes;

  protected $table = 'dispositivolink';
  protected $primaryKey = 'token';
  public $incrementing = false;
  protected $dates = ['deleted_at', 'created_at', 'updated_at', 'expire_at'];
  protected $guarded = ['token'];
  protected $fillable = [
    'token',
    'uuid',
    'email',
    'expired',
    'expire_at'
  ];

  public function export($complete = true)
  {
      $dados = $this->toArray();
      $dados['created_at'] = $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null;
      $dados['updated_at'] = $this->updated_at ? $this->updated_at->format('Y-m-d H:i:s') : null;
      $dados['expire_at'] = $this->expire_at ? $this->expire_at->format('Y-m-d H:i:s') : null;
      unset($dados['deleted_at']);
      unset($dados['uuid']);
      return $dados;
  }
  public function setuuidAttribute($value)
  {
      $this->attributes['uuid'] = utf8_decode($value);
  }
  public function setemailAttribute($value)
  {
      $this->attributes['email'] = utf8_decode($value);
  }
}
