<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Regiao extends Model
{
    protected $table = 'regiao';

    protected $hidden = ['created_usuarioid', 'updated_usuarioid'];


    public function toObject($showcompact = true)
    {
        $dados = $this->toArray();
        if ($showcompact) {
            unset($dados['created_at']);
            unset($dados['updated_at']);
        } else {
            $dados['created_usuario'] = $this->created_usuario ? $this->created_usuario->toObject(false) : null;
            $dados['updated_usuario'] = $this->updated_usuario ? $this->updated_usuario->toObject(false) : null;
        }
        $dados['cidadescount'] = $this->cidades ? $this->cidades->count() : 0;
        unset($dados['created_usuarioid']);
        unset($dados['updated_usuarioid']);
        return $dados;
    }

    public function toSmallObject()
    {
        $dados = [
            'id' => $this->id,
            'regiao' => $this->regiao,
            'sugerirmotorista' => $this->sugerirmotorista
        ];
        return $dados;
    }

    public function setieAttribute($value)
    {
        $ie = isset($value) ? trim($value) : '';
        if ($ie=='') $ie=null;
        $this->attributes['regiao'] =  $ie ;
    }

    public function getregiaoAttribute($value)
    {
      return utf8_encode($value);
    }
    public function setregiaoAttribute($value)
    {
      $this->attributes['regiao'] =  utf8_decode($value);
    }


    public function cidades()
    {
        return $this->hasMany(Cidades::class, 'regiaoid', 'id', 'cidadeid')->orderBy('cidade','asc');
    }

    public function created_usuario()
    {
        return $this->hasOne(Usuario::class, 'id', 'created_usuarioid');
    }

    public function updated_usuario()
    {
        return $this->hasOne(Usuario::class, 'id', 'updated_usuarioid');
    }


}
