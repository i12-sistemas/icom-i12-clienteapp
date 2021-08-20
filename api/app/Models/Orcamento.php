<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

use App\Enums\ColetasStatusVirtualType;

class Orcamento extends Model
{
    protected $table = 'orcamento';

    protected $dates = ['created_at', 'updated_at', 'dhcoleta', 'updatedstatus_at'];

    public function export($complete = false)
    {
        $dados = $this->toArray();

        $dados['endcoleta_cidade'] = $this->coletacidade ? $this->coletacidade->toSmallObject() : null;
        if ($this->coletaregiao) $dados['endcoleta_regiao'] = $this->coletaregiao ? $this->coletaregiao->toSmallObject() : null;
        unset($dados['endcoleta_cidadeid']);


        $dados['clienteorigem'] = $this->clienteorigem ? $this->clienteorigem->toSmallObject() : null;
        unset($dados['origemclienteid']);

        $dados['clientedestino'] = $this->clientedestino ? $this->clientedestino->toSmallObject() : null;
        unset($dados['destinoclienteid']);

        if ($this->motoristaid > 0) $dados['motorista'] = $this->motorista ? $this->motorista->exportsmall() : null;
        unset($dados['motoristaid']);

        $dados['created_usuario'] = $this->created_usuario ? $this->created_usuario->toObject(false) : null;
        $dados['updated_usuario'] = $this->updated_usuario ? $this->updated_usuario->toObject(false) : null;
        $dados['updatedstatus_usuario'] = $this->updatedstatus_usuario ? $this->updatedstatus_usuario->toObject(false) : null;
        unset($dados['created_usuarioid']);
        unset($dados['updated_usuarioid']);
        unset($dados['updatedstatus_usuarioid']);
        return $dados;
    }


    public function toObject($showCompact = false)
    {
        $dados = $this->toArray();

        if ($this->coletacidade) {
            if ($showCompact) {
                $dados['endcoleta_cidade'] = $this->coletacidade->toSmallObject(true);
            } else {
                $dados['endcoleta_cidade'] = $this->coletacidade->toObject(true);
            }
        }
        if ($this->coletaregiao) $dados['endcoleta_regiao'] = $this->coletaregiao->toSmallObject();
        unset($dados['endcoleta_cidadeid']);

        if ($this->clienteorigem) {
            if ($showCompact) {
                $dados['clienteorigem'] = $this->clienteorigem->toSmallObject();
            } else {
                $dados['clienteorigem'] = $this->clienteorigem->toObject(false);
            }
        }
        unset($dados['origemclienteid']);

        if ($showCompact) {
            $dados['clientedestino'] = $this->clientedestino? $this->clientedestino->toSmallObject() : null;
        } else {
            $dados['clientedestino'] = $this->clientedestino ? $this->clientedestino->toObject(false) : null;
        }
        unset($dados['destinoclienteid']);

        if ($this->motoristaid > 0) {
            $dados['motorista'] = $this->motorista ? $this->motorista->toObject($showCompact) : null;
        }
        unset($dados['motoristaid']);



        $dados['itenscount'] = $this->itens->count();

        if (!$showCompact) {
            $aItens = [];
            foreach ($this->itens as $e) {
                $aE = $e->toObject($showCompact);
                $aItens[] = $aE;
            }
            $dados['itens'] = $aItens;
        } else {
            unset($dados['itens']);
        }


        $dados['created_usuario'] = $this->created_usuario ? $this->created_usuario->toObject(false) : true;
        $dados['updated_usuario'] = $this->updated_usuario ? $this->updated_usuario->toObject(false) : true;
        $dados['updatedstatus_usuario'] = $this->updated_usuario ? $this->updated_usuario->toObject(false) : true;
        unset($dados['created_usuarioid']);
        unset($dados['updated_usuarioid']);
        unset($dados['updatedstatus_usuarioid']);
        return $dados;
    }

    public function toSmallObject()
    {
        $dados = $this->toArray();
        $dados['endcoleta_cidade'] = $this->coletacidade ? $this->coletacidade->toSmallObject() : null;
        $dados['endcoleta_regiao'] = $this->coletaregiao ? $this->coletaregiao->toSmallObject() : null;
        unset($dados['endcoleta_cidadeid']);

        $dados['clienteorigem'] = $this->clienteorigem ? $this->clienteorigem->toSmallObject() : null;
        unset($dados['origemclienteid']);

        $dados['clientedestino'] = $this->clientedestino ? $this->clientedestino->toSmallObject() : null;
        unset($dados['destinoclienteid']);

        if ($this->motoristaid > 0) {
            $dados['motorista'] = $this->motorista ? $this->motorista->toObject($showCompact) : null;
        }
        unset($dados['motoristaid']);

        unset($dados['itens']);
        unset($dados['created_usuarioid']);
        unset($dados['updated_usuarioid']);
        unset($dados['updatedstatus_usuarioid']);
        return $dados;
    }


    public function clienteorigem()
    {
        return $this->hasOne(Cliente::class, 'id', 'origemclienteid')->with('cidade');
    }

    public function clientedestino()
    {
        return $this->hasOne(Cliente::class, 'id', 'destinoclienteid')->with('cidade');
    }

    public function motorista()
    {
        return $this->hasOne(Motorista::class, 'id', 'motoristaid');
    }

    public function itens()
    {
        return $this->hasMany(OrcamentoItens::class, 'orcamentoid', 'id');
    }

    public function coleta()
    {
        return $this->hasOne(Coletas::class, 'id', 'coletaid');
    }

    public function created_usuario()
    {
        return $this->hasOne(Usuario::class, 'id', 'created_usuarioid');
    }

    public function updated_usuario()
    {
        return $this->hasOne(Usuario::class, 'id', 'updated_usuarioid');
    }

    public function updatedstatus_usuario()
    {
        return $this->hasOne(Usuario::class, 'id', 'updatedstatus_usuarioid');
    }


    public function coletacidade()
    {
        return $this->hasOne(Cidades::class, 'id', 'endcoleta_cidadeid');
    }

    public function coletaregiao()
    {
        return $this->hasOneThrough(
            Regiao::class,
            Cidades::class,
            'id',
            'id',
            'endcoleta_cidadeid',
            'regiaoid'
        );
    }

    public function getobscoletaAttribute($value)
    {
      return utf8_encode($value);
    }
    public function setobscoletaAttribute($value)
    {
      $this->attributes['obscoleta'] =  utf8_decode($value);
    }

    public function getobsorcamentoAttribute($value)
    {
      return utf8_encode($value);
    }
    public function setobsorcamentoAttribute($value)
    {
      $this->attributes['obsorcamento'] =  utf8_decode($value);
    }



    public function getgestaoclienteItenscompradorAttribute($value)
    {
      return utf8_encode($value);
    }
    public function setgestaoclienteItenscompradorAttribute($value)
    {
      $this->attributes['gestaocliente_itenscomprador'] =  utf8_decode($value);
    }

    public function getgestaoclienteOrdemcompraAttribute($value)
    {
      return utf8_encode($value);
    }
    public function setgestaoclienteOrdemcompraAttribute($value)
    {
      $this->attributes['gestaocliente_ordemcompra'] =  utf8_decode($value);
    }


    public function getendcoletaNumeroAttribute($value)
    {
      return utf8_encode($value);
    }
    public function setendcoletaNumeroAttribute($value)
    {
      $this->attributes['endcoleta_numero'] =  utf8_decode($value);
    }


    public function getgestaoclienteCompradorAttribute($value)
    {
      return utf8_encode($value);
    }
    public function setgestaoclienteCompradorAttribute($value)
    {
      $this->attributes['gestaocliente_comprador'] =  utf8_decode($value);
    }


    public function getespecieAttribute($value)
    {
      return utf8_encode($value);
    }
    public function setespecieAttribute($value)
    {
      $this->attributes['especie'] =  utf8_decode($value);
    }


    public function getendcoletaLogradouroAttribute($value)
    {
      return utf8_encode($value);
    }
    public function setendcoletaLogradouroAttribute($value)
    {
      $this->attributes['endcoleta_logradouro'] =  utf8_decode($value);
    }

    public function getenderecoenumeroAttribute($value) {
        //não usar utf8_encode pois apresenta erro na view de PDF
        $end = $this->endcoleta_logradouro . ' ' . $this->endcoleta_endereco . ', ' . $this->endcoleta_numero;
        return $end;
    }


    public function getendcoletaEnderecoAttribute($value)
    {
      return utf8_encode($value);
    }
    public function setendcoletaEnderecoAttribute($value)
    {
      $this->attributes['endcoleta_endereco'] =  utf8_decode($value);
    }


    public function getendcoletaComplementoAttribute($value)
    {
      return utf8_encode($value);
    }
    public function setendcoletaComplementoAttribute($value)
    {
      $this->attributes['endcoleta_complemento'] =  utf8_decode($value);
    }


    public function getcontatonomeAttribute($value)
    {
      return utf8_encode($value);
    }
    public function setcontatonomeAttribute($value)
    {
      $this->attributes['contatonome'] =  utf8_decode($value);
    }


    public function getcontatoemailAttribute($value)
    {
      return utf8_encode($value);
    }
    public function setcontatoemailAttribute($value)
    {
      $this->attributes['contatoemail'] =  utf8_decode($value);
    }


    public function getendcoletaBairroAttribute($value)
    {
      return utf8_encode($value);
    }

    public function setendcoletaBairroAttribute($value)
    {
        $this->attributes['endcoleta_bairro'] =  utf8_decode($value);
    }


    public function getendcoletaCepAttribute($value)
    {
      return utf8_encode($value);
    }
    public function setendcoletaCepAttribute($value)
    {
      $this->attributes['endcoleta_cep'] =  utf8_decode($value);
    }

    public function getjustsituacaoAttribute($value)
    {
      return utf8_encode($value);
    }
    public function setjustsituacaoAttribute($value)
    {
      $this->attributes['justsituacao'] =  utf8_decode($value);
    }

}
