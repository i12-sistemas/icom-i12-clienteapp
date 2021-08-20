<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

use App\Enums\ColetasStatusVirtualType;

class Coletas extends Model
{

    protected $table = 'coletas';

    protected $dates = ['dhcoleta', 'dhbaixa'];

    public function export($complete = false)
    {
        $dados = $this->toArray();
        $dados['dhcoleta'] = $this->dhcoleta ? $this->dhcoleta->format('Y-m-d') : null;
        $dados['dhbaixa'] = $this->dhbaixa ? $this->dhbaixa->format('Y-m-d H:i:s') : null;

        $dados['endcoleta_cidade'] = $this->coletacidade ? $this->coletacidade->toSmallObject() : null;

        if ($this->coletaregiao) $dados['endcoleta_regiao'] = $this->coletaregiao ? $this->coletaregiao->toSmallObject() : null;
        unset($dados['endcoleta_cidadeid']);

        $dados['clienteorigem'] = $this->clienteorigem ? $this->clienteorigem->toSmallObject() : null;
        unset($dados['origemclienteid']);

        $dados['clientedestino'] = $this->clientedestino ? $this->clientedestino->toSmallObject() : null;
        unset($dados['destinoclienteid']);

        if ($this->motoristaid > 0) $dados['motorista'] = $this->motorista ? $this->motorista->exportsmall() : null;
        unset($dados['motoristaid']);

        //1=interno direto, 2=interno orcamento, 3=painel do cliente, 4=Coleta avulsa aplicativo
        if ($this->origem === '2')
            $dados['orcamentoid'] = $this->orcamento ? $this->orcamento->id : null;

        unset($dados['orcamento']);
        $dados['itenscount'] = $this->itens->count();
        $dados['created_usuario'] = $this->created_usuario->toObject(false);
        $dados['updated_usuario'] = $this->updated_usuario->toObject(false);
        unset($dados['created_usuarioid']);
        unset($dados['updated_usuarioid']);
        unset($dados['eventos']);

        return $dados;
    }

    public function toObject($showCompact = false)
    {
        $dados = $this->toArray();
        $dados['dhcoleta'] = $this->dhcoleta ? $this->dhcoleta->format('Y-m-d') : null;
        $dados['dhbaixa'] = $this->dhbaixa ? $this->dhbaixa->format('Y-m-d H:i:s') : null;

        if ($showCompact) {
            $dados['endcoleta_cidade'] = $this->coletacidade ? $this->coletacidade->toSmallObject() : null;
        } else {
            $dados['endcoleta_cidade'] = $this->coletacidade ? $this->coletacidade->toObject(true) : null;
        }
        if ($this->coletaregiao) $dados['endcoleta_regiao'] = $this->coletaregiao ? $this->coletaregiao->toSmallObject() : null;
        unset($dados['endcoleta_cidadeid']);

        if ($showCompact) {
            $dados['clienteorigem'] = $this->clienteorigem ? $this->clienteorigem->toSmallObject() : null;
        } else {
            $dados['clienteorigem'] = $this->clienteorigem ? $this->clienteorigem->toObject(false) : null;
        }
        unset($dados['origemclienteid']);

        if ($showCompact) {
            $dados['clientedestino'] = $this->clientedestino ? $this->clientedestino->toSmallObject() : null;
        } else {
            $dados['clientedestino'] = $this->clientedestino ? $this->clientedestino->toObject(false) : null;
        }
        unset($dados['destinoclienteid']);

        if ($this->motoristaid > 0) {
            $dados['motorista'] = $this->motorista ? $this->motorista->toObject($showCompact) : null;
        }
        unset($dados['motoristaid']);

        //1=interno direto, 2=interno orcamento, 3=painel do cliente, 4=Coleta avulsa aplicativo
        if ($this->origem == '2') {
            $dados['orcamentoid'] = $this->orcamento ? $this->orcamento->id : null;
        }



        $dados['itenscount'] = $this->itens->count();

        if (!$showCompact) {
            $aItens = [];
            foreach ($this->itens as $e) {
                $aE = $e->toObject(false);
                $aItens[] = $aE;
            }
            $dados['itens'] = $aItens;
        } else {
            unset($dados['itens']);
        }

        $dados['notascount'] = $this->notas->count();

        $aItens = [];
        foreach ($this->notas as $e) {
            $aE = $e->toArray();
            $aItens[] = $aE;
        }
        $dados['notas'] = $aItens;


        $dados['created_usuario'] = $this->created_usuario->toObject(false);
        $dados['updated_usuario'] = $this->updated_usuario->toObject(false);
        unset($dados['created_usuarioid']);
        unset($dados['updated_usuarioid']);
        unset($dados['eventos']);


        // if ($showCompact) {
        //     unset($dados['cnpjmemo']);
        // }
        return $dados;
    }

    public function exportMotorista($complete = false)
    {
        $motorista = null;
        if ($this->motorista) {
            $motorista = $this->motorista->toObject($complete);
            $motorista['username'] = $this->motorista->username;
        }
        $dados = [
            'id' => $this->id,
            'chavenota' => $this->chavenota,
            'idmotorista' => $this->motoristaid,
            'motorista' => $motorista,

            'remetenteid' => $this->origemclienteid,
            'remetente' => $this->clienteorigem ? $this->clienteorigem->toObject(false) : null,

            'destinatarioid' => $this->destinoclienteid,
            'destinatario' => $this->clientedestino ? $this->clientedestino->toObject(false) : null,

            'produtoperigoso' => $this->produtosperigosos,
            'urgente' => $this->cargaurgente,
            'exclusivo' => $this->veiculoexclusico,

            'datacoleta' => $this->dhcoleta ? $this->dhcoleta->format('Y-m-d') : null,
            'created_at' => $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null,
            'dhinclusao' => $this->dhinclusao ? $this->dhinclusao->format('Y-m-d H:i:s') : null,
            'updated_at' => $this->updated_at ? $this->updated_at->format('Y-m-d H:i:s') : null,

            'peso' => $this->peso,
            'quantidade' => $this->qtde,
            'especie' => $this->especie,
            'observacao' => $this->obs,
            'contato' => $this->contatonome,

            'liberado' => $this->situacao === '1',
            'encerrado' => (($this->situacao === '2') || ($this->situacao === '3')),
            'status' => $this->situacao,

            'endereco' => $this->enderecoenumero,
            'bairro' => $this->endcoleta_bairro,
            'cidade' => $this->coletacidade ? $this->coletacidade->cidade : null,
            'uf' => $this->coletacidade ? $this->coletacidade->uf : null
            // ,,,,,,,,,,,,,lat,lng,googlemsg,,,filtrocliente,,,,sync,infomaps
        ];

        // $dados['itenscount'] = $this->itens->count();

        // if (!$complete) {
        //     $aItens = [];
        //     foreach ($this->itens as $e) {
        //         $aE = $e->toObject($complete);
        //         $aItens[] = $aE;
        //     }
        //     $dados['itens'] = $aItens;
        // } else {
        //     unset($dados['itens']);
        // }

        return $dados;
    }

    public function toSmallObject()
    {
        $dados = $this->toArray();
        $dados['dhcoleta'] = $this->dhcoleta ? $this->dhcoleta->format('Y-m-d') : null;
        $dados['dhbaixa'] = $this->dhbaixa ? $this->dhbaixa->format('Y-m-d H:i:s') : null;

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
        unset($dados['eventos']);

        unset($dados['created_usuarioid']);
        unset($dados['updated_usuarioid']);

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
        return $this->hasMany(ColetasItens::class, 'coletaid', 'id');
    }

    public function eventos()
    {
        return $this->hasMany(ColetasEventos::class, 'coletaid', 'id')->with('created_usuario');
    }


    public function notas()
    {
        return $this->hasMany(ColetasNota::class, 'idcoleta', 'id');
    }

    public function orcamento()
    {
        return $this->hasOne(Orcamento::class, 'coletaid', 'id');
    }

    public function created_usuario()
    {
        return $this->hasOne(Usuario::class, 'id', 'created_usuarioid');
    }

    public function updated_usuario()
    {
        return $this->hasOne(Usuario::class, 'id', 'updated_usuarioid');
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

    public function getstatusvirtualAttribute($value)
    {
        // const Aberto_RevOrcamento = "1";
        // const Aberto_NaoLiberado = "2";
        // const Aberto_Atrasado = "3";
        // const Aberto_Ontem = "4";
        // const Aberto_Hoje = "5";
        // const Aberto_Futuro = "6";
        // const Encerrado_Interno = "7";
        // const Encerrado_Motorista = "8";
        $i = ColetasStatusVirtualType::Aberto_NaoLiberado;

        if ($this->situacao == 0) {
            // REVISARORCAMENTO: 1,
            // NAOLIBERADO: 2,
            // ATRASADA: 3,
            // ONTEM: 4,
            // HOJE: 5,
            // FUTURA: 6,


            // se veio de orçamento
            if ($this->origem == '2') {
                if (!$this->liberado) $i = ColetasStatusVirtualType::Aberto_RevOrcamento; //PENDENTE REVISAR ORÇAMENTO
            } else {
                if (!$this->liberado) {
                    $i = ColetasStatusVirtualType::Aberto_NaoLiberado;
                } else {
                    $date =Carbon::parse(Carbon::now()->toDateString());
                    $diffDay = $this->dhcoleta->diffInDays($date, false);
                    if ($diffDay < 0 ) {
                        $i = ColetasStatusVirtualType::Aberto_Futuro;
                    } elseif ($diffDay == 0 ) {
                        $i = ColetasStatusVirtualType::Aberto_Hoje;
                    } elseif ($diffDay == 1 ) {
                        $i = ColetasStatusVirtualType::Aberto_Ontem;
                    } elseif ($diffDay > 1 ) {
                        $i = ColetasStatusVirtualType::Aberto_Atrasado;
                    }
                }
            }
        } elseif ($this->situacao = 1) {
            $i = ColetasStatusVirtualType::Encerrado_Interno;
        }

      return $i;
    }

    public function getobsAttribute($value)
    {
      return utf8_encode($value);
    }
    public function setobsAttribute($value)
    {
      $this->attributes['obs'] =  utf8_decode($value);
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


    public function getchavenotaAttribute($value)
    {
      return utf8_encode($value);
    }
    public function setchavenotaAttribute($value)
    {
        if ($value) {
            $this->attributes['chavenota'] =  $value == '' ? null : utf8_decode($value);
        } else {
            $this->attributes['chavenota'] =  null;
        }
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

    public function getenderecoenumeroAttribute($value) {
        //não usar utf8_encode pois apresenta erro na view de PDF
        $end = ($this->endcoleta_logradouro == '' ? '' : $this->endcoleta_logradouro . ' ') . $this->endcoleta_endereco .
            ($this->endcoleta_numero === '' ? '' : ', ' . $this->endcoleta_numero);
        return $end;
    }

}

