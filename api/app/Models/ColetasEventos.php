<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ColetasEventos extends Model
{
    protected $table = 'coletas_eventos';
    public $timestamps = false;
    protected $dates = ['created_at'];
    protected $hidden = ['created_usuarioid'];

    public function toObject($showCompact = false)
    {
        $dados = [
            'id' => $this->id,
            'tipo' => $this->tipo,
            'created_at' => $this->created_at,
            'detalhe' => $this->detalhe,
            'ip' => $this->ip,
            'data' => $this->datajson,
            'created_usuario' => $this->created_usuario ? $this->created_usuario->toObject(false) : null,
        ];
        return $dados;
    }

    public function created_usuario()
    {
        return $this->hasOne(Usuario::class, 'id', 'created_usuarioid');
    }

    public function gettipoAttribute($value)
    {
      return utf8_encode($value);
    }
    public function settipoAttribute($value)
    {
      $this->attributes['tipo'] =  utf8_decode($value);
    }

    public function getdetalheAttribute($value)
    {
      return utf8_encode($value);
    }
    public function setdetalheAttribute($value)
    {
      $this->attributes['detalhe'] =  utf8_decode($value);
    }


}
