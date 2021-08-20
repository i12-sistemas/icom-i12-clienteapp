<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DespesaViagem extends Model
{
    protected $table = 'despesaviagem';

    public function export($complete = true)
    {
        $dados = $this->toArray();
        $dados['created_at'] = $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null;
        $dados['updated_at'] = $this->updated_at ? $this->updated_at->format('Y-m-d H:i:s') : null;
        unset($dados['created_usuarioid']);
        unset($dados['updated_usuarioid']);
        if ($complete) {
            $dados['created_usuario'] = $this->created_usuario ? $this->created_usuario->toObject(false) : null;
            $dados['updated_usuario'] = $this->updated_usuario ? $this->updated_usuario->toObject(false) : null;
        }
        return $dados;
    }

    public function acertos()
    {
        return $this->belongsTo(AcertoViagemDespesas::class, 'id', 'despesaviagemid');
    }

    public function created_usuario()
    {
        return $this->hasOne(Usuario::class, 'id', 'created_usuarioid');
    }

    public function updated_usuario()
    {
        return $this->hasOne(Usuario::class, 'id', 'updated_usuarioid');
    }

    public function getdescricaoAttribute($value)
    {
      return utf8_encode($value);
    }
    public function setdescricaoAttribute($value)
    {
      $this->attributes['descricao'] =  utf8_decode($value);
    }
}
