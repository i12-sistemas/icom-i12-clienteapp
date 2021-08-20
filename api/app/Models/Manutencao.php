<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Manutencao extends Model
{
    protected $table = 'manutencao';
    protected $dates = ['created_at', 'updated_at', 'limitedata', 'alertadata'];

    public function toObject($showCompact = false)
    {
        $dados = $this->toArray();

        if ($this->veiculoid > 0) $dados['veiculo'] = $this->veiculo->toObject($showCompact);
        unset($dados['veiculoid']);

        if ($this->servicoid > 0) $dados['servico'] = $this->servico->toObject($showCompact);
        unset($dados['servicoid']);

        $dados['created_usuario'] = $this->created_usuario->toObject(false);
        $dados['updated_usuario'] = $this->updated_usuario->toObject(false);
        unset($dados['created_usuarioid']);
        unset($dados['updated_usuarioid']);

        $dados['limitekm'] = $this->limitekm;  //kmlimite
        $dados['limitedata'] = $this->limitedata ? $this->limitedata->format('Y-m-d') : null; //datalimite

        $dados['alertakm'] = $this->alertakm; //kmalerta
        $dados['alertadata'] = $this->alertadata ? $this->alertadata->format('Y-m-d') : null; //dataalerta

        return $dados;
    }

    public function getobsAttribute($value)
    {
      return utf8_encode($value);
    }
    public function setobsAttribute($value)
    {
      $this->attributes['obs'] =  utf8_decode($value);
    }


    public function getcodpecaAttribute($value)
    {
      return utf8_encode($value);
    }
    public function setcodpecaAttribute($value)
    {
      $this->attributes['codpeca'] =  utf8_decode($value);
    }



    public function created_usuario()
    {
        return $this->hasOne(Usuario::class, 'id', 'created_usuarioid');
    }

    public function updated_usuario()
    {
        return $this->hasOne(Usuario::class, 'id', 'updated_usuarioid');
    }

    public function veiculo()
    {
        return $this->hasOne(Veiculo::class, 'id', 'veiculoid');
    }

    public function servico()
    {
        return $this->hasOne(ManutencaoServicos::class, 'id', 'servicoid');
    }

}
