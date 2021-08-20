<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GuaritaCheck extends Model
{
    protected $table = 'guaritacheck';
    public $timestamps = true;


    public function export($complete = true)
    {
        $dados = $this->toArray();
        $dados['created_at'] = $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null;
        $dados['updated_at'] = $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null;
        if ($complete) {
            $dados['usuario'] = $this->usuario ? $this->usuario->toSimple() : null;
        }

        $dados['motorista'] = $this->motorista ? $this->motorista->exportsmall() : null;
        $dados['veiculo'] = $this->veiculo ? $this->veiculo->toObject(false) : null;
        $dados['unidade'] = $this->unidade ? $this->unidade->toSimple() : null;
        unset($dados['unidadeid']);
        unset($dados['motoristaid']);
        unset($dados['veiculoid']);
        unset($dados['userid']);

        if ($complete) {
          $aItens = [];
          if ($this->itens) {
            $count = count($this->itens);
            foreach ($this->itens as $key =>$item) {
              $a = $item->export(true);
              $a['nordem'] = $count-$key;
              $aItens[] = $a;
            }

          }
          $dados['itens'] = $aItens;
        }
        return $dados;
    }

    public function totaliza()
    {
        $q = $this->itens()->where('erro', '=', 0)->count();
        $this->attributes['motoristalock'] =  (($q > 0) && ($this->motoristaid) && ($this->veiculoid)) ? 1 : 0;
    }

    public function itens()
    {
        return $this->hasMany(GuaritaCheckItem::class, 'guaritacheckid', 'id')->orderBy('id', 'DESC');
    }


    public function motorista()
    {
        return $this->hasOne(Motorista::class, 'id', 'motoristaid');
    }


    public function veiculo()
    {
        return $this->hasOne(Veiculo::class, 'id', 'veiculoid');
    }


    public function unidade()
    {
        return $this->hasOne(Unidade::class, 'id', 'unidadeid');
    }

    public function usuario()
    {
        return $this->hasOne(Usuario::class, 'id', 'userid');
    }


    public function geterromsgAttribute($value)
    {
      return utf8_encode($value);
    }
    public function seterromsgAttribute($value)
    {
      $this->attributes['erromsg'] =  utf8_decode($value);
    }

}
