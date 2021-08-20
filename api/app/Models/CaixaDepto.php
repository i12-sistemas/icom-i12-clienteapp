<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CaixaDepto extends Model
{
    protected $table = 'caixa_depto';

    public function export($complete = true)
    {
        $dados = [
            'id'  =>  $this->id,
            'depto'  =>  $this->depto,
            'ativo'  =>  $this->ativo,
            'usuarioscount' => $this->usuarios->count()
        ];
        if ($complete) {
            $dados['created_at'] = $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null;
            $dados['updated_at'] = $this->updated_at ? $this->updated_at->format('Y-m-d H:i:s') : null;
            $dados['created_usuario'] = $this->created_usuario ? $this->created_usuario->toObject(false) : null;
            $dados['updated_usuario'] = $this->updated_usuario ? $this->updated_usuario->toObject(false) : null;
            unset($dados['created_usuarioid']);
            unset($dados['updated_usuarioid']);

            $aItens = [];
            foreach ($this->usuarios as $e) {
                $aE = $e->export(false);
                $aItens[] = $aE;
            }
            $dados['usuarios'] = $aItens;

        }
        return $dados;
    }

    public function usuarios()
    {
        return $this->hasMany(CaixaDeptoUsuario::class, 'caixadeptoid', 'id')->with('usuario', 'created_usuario')->orderBy('created_at', 'desc');
    }

    public function caixas()
    {
        return $this->belongsTo(Caixa::class, 'id', 'deptoid');
    }


    public function created_usuario()
    {
        return $this->hasOne(Usuario::class, 'id', 'created_usuarioid');
    }

    public function updated_usuario()
    {
        return $this->hasOne(Usuario::class, 'id', 'updated_usuarioid');
    }

    public function getdeptoAttribute($value)
    {
      return utf8_encode($value);
    }
    public function setdeptoAttribute($value)
    {
      $this->attributes['depto'] =  utf8_decode($value);
    }
}
