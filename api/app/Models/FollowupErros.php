<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FollowupErros extends Model
{
    protected $table = 'followup_erros';

    public function export($complete = false)
    {
        $dados = $this->toArray();
        $dados['created_at'] = $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null;
        $dados['updated_at'] = $this->updated_at ? $this->updated_at->format('Y-m-d H:i:s') : null;

        $dados['created_usuario'] = $this->created_usuario ? $this->created_usuario->toObject(false) : null;
        unset($dados['created_usuarioid']);
        $dados['updated_usuario'] = $this->updated_usuario ? $this->updated_usuario->toObject(false) : null;
        unset($dados['updated_usuarioid']);

        return $dados;
    }


    public function followup()
    {
        if ($this->tipo === 'agenda') {
            return $this->hasMany(Followup::class, 'erroagendaid', 'id');
        } else if ($this->tipo === 'coleta') {
            return $this->hasMany(Followup::class, 'errocoletaid', 'id');
        } else if ($this->tipo === 'dtpromessa') {
            return $this->hasMany(Followup::class, 'errodtpromessaid', 'id');
        }
    }

    public function created_usuario()
    {
        return $this->hasOne(Usuario::class, 'id', 'created_usuarioid');
    }

    public function updated_usuario()
    {
        return $this->hasOne(Usuario::class, 'id', 'updated_usuarioid');
    }


    public function setdescricaoAttribute($value)
    {
        $this->attributes['descricao'] =  utf8_decode(mb_strtoupper($value));
    }
    public function getdescricaoAttribute($value)
    {
      return utf8_encode($value);
    }

}
