<?php

namespace App\Models;
use App\Models\Cliente;
use App\Models\ColetasNota;


use Illuminate\Database\Eloquent\Model;

class GuaritaCheckItem extends Model
{
    protected $table = 'guaritacheckitem';
    public $dates = ['created_at'];
    public $timestamps = false;

    public function export($complete = true)
    {
        $dados = $this->toArray();
        $dados['created_at'] = $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null;
        if ($complete) {
            $dados['usuario'] = $this->usuario ? $this->usuario->toSimple() : null;
        }
        unset($dados['guaritacheckid']);
        unset($dados['userid']);
        return $dados;
    }

    public function geterromsgAttribute($value)
    {
      return utf8_encode($value);
    }
    public function seterromsgAttribute($value)
    {
      $this->attributes['erromsg'] = ($value ? $value !== '' : false) ? utf8_decode($value) : null;
    }

    public function usuario()
    {
        return $this->hasOne(Usuario::class, 'id', 'userid');
    }


    public function coletanota()
    {
        return $this->hasOne(ColetasNota::class, 'notachave', 'nfechave');
    }



    public function setnfechaveAttribute($value)
    {
      $nfe = decodeChaveNFe($value);
      $this->attributes['nfechave'] =  utf8_decode($value);
      $this->nfecnpj =  $nfe['CNPJ'];
      $this->nfenumero =  $nfe['nNF'];

      $cliente = Cliente::where('cnpj', '=', $nfe['CNPJ'])->first();
      if ($cliente) {
        $this->clienteid =  $cliente->id;
      } else {
        $this->clienteid =  null;
      }

      $nota = ColetasNota::where('notachave', '=', $value)->first();

      $this->attributes['erro'] = ($nota ? $nota->id > 0 : false) ? 0 : 1;

      if ($nota ? $nota->id > 0 : false) {
        $this->erromsg = null;
      } else {
        $this->erromsg = 'NF-e não foi encontrada!';
      }

    }

}
