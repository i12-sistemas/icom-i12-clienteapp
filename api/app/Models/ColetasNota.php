<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Exception;
use App\Models\Cliente;
use Carbon\Carbon;

class ColetasNota extends Model
{
    protected $table = 'coletas_nota';
    protected $dates = ['dhlocal_data', 'dhlocal_created_at', 'created_at', 'updated_at', 'baixanfedhproc'];

    public function export($complete = true)
    {
        $dados = $this->toArray();
        $dados['motorista'] = $this->motorista ? $this->motorista->exportsmall() : null;
        unset($dados['motoristaid']);

        if ($this->coleta) {
            $dados['coleta'] = [
                'id' => $this->coleta->id,
                'cnpjorigem' => $this->coleta->clienteorigem ? $this->coleta->clienteorigem->cnpj : null,
                'cnpjdestino' => $this->coleta->clientedestino ? $this->coleta->clientedestino->cnpj : null,
            ];
        }

        $dados['baixanfedhproc'] = $this->baixanfedhproc ? $this->baixanfedhproc->format('Y-m-d H:i:s') : null;
        $dados['dhlocal_data'] = $this->dhlocal_data ? $this->dhlocal_data->format('Y-m-d H:i:s') : null;
        $dados['dhlocal_created_at'] = $this->dhlocal_created_at ? $this->dhlocal_created_at->format('Y-m-d H:i:s') : null;
        $dados['created_at'] = $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null;
        $dados['updated_at'] = $this->updated_at ? $this->updated_at->format('Y-m-d H:i:s') : null;

        return $dados;
    }

    public function identificaRemetente()
    {
        try {
            $r = false;
            if ($this->remetenteid > 0) {
                $r = true;
                throw new Exception('');
            }
            if ($this->notachave ?  $this->notachave === '' : false) throw new Exception('Nota sem chave');

            $ch = decodeChaveNFe($this->notachave);
            $this->attributes['notanumero'] = $ch['nNF'];
            $this->attributes['notaserie'] = $ch['serie'];
            $this->attributes['remetentecnpj'] =  $ch['CNPJ'];
            $cliente = Cliente::where('cnpj', '=', $ch['CNPJ'])->where('ativo', '=', 1)->first();
            if ($cliente) {
                $this->remetenteid =  $cliente->id;
                $this->remetentenome =  $cliente->razaosocial;
            }
        } catch (\Throwable $th) {
            if ($th->getMessage() !== '') \Log::debug($th->getMessage());
        }
        return $r;
    }


    public function coleta()
    {
        return $this->hasOne(Coletas::class, 'id', 'idcoleta')->with('clienteorigem', 'clientedestino');
    }


    public function motorista()
    {
        return $this->hasOne(Motorista::class, 'id', 'motoristaid');
    }

    public function cidade()
    {
        return $this->hasOne(Cidades::class, 'codigo_ibge', 'endcoleta_cidadecodibge');
    }

    public function setremetentecnpjAttribute($value)
    {
        $this->attributes['remetentecnpj'] = utf8_decode($value);
    }
    public function setdestinatariocnpjAttribute($value)
    {
        $this->attributes['destinatariocnpj'] = utf8_decode($value);
    }
    public function setremetentenomeAttribute($value)
    {
        $this->attributes['remetentenome'] = utf8_decode($value);
    }
    public function setdestinatarionomeAttribute($value)
    {
        $this->attributes['destinatarionome'] = utf8_decode($value);
    }
    public function setgeoErrorAttribute($value)
    {
        $this->attributes['geo_error'] = utf8_decode($value);
    }
    public function setobsAttribute($value)
    {
        $this->attributes['obs'] = utf8_decode($value);
    }

    public function getremetentecnpjAttribute($value)
    {
      return utf8_encode($value);
    }
    public function getdestinatariocnpjAttribute($value)
    {
      return utf8_encode($value);
    }
    public function getremetentenomeAttribute($value)
    {
      return utf8_encode($value);
    }
    public function getdestinatarionomeAttribute($value)
    {
      return utf8_encode($value);
    }
    public function getgeoErrorAttribute($value)
    {
      return utf8_encode($value);
    }
    public function getobsAttribute($value)
    {
      return utf8_encode($value);
    }


    public function getendcoletaEnderecoAttribute($value)
    {
      return utf8_encode($value);
    }
    public function setendcoletaEnderecoAttribute($value)
    {
      $this->attributes['endcoleta_endereco'] =  utf8_decode($value);
    }

    public function getendcoletaNumeroAttribute($value)
    {
      return utf8_encode($value);
    }
    public function setendcoletaNumeroAttribute($value)
    {
      $this->attributes['endcoleta_numero'] =  utf8_decode($value);
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

    public function getendcoletaComplementoAttribute($value)
    {
      return utf8_encode($value);
    }
    public function setendcoletaComplementoAttribute($value)
    {
      $this->attributes['endcoleta_complemento'] =  utf8_decode($value);
    }

    public function getcoletaavulsaerrormsgAttribute($value)
    {
      return utf8_encode($value);
    }
    public function setcoletaavulsaerrormsgAttribute($value)
    {
      $this->attributes['coletaavulsaerrormsg'] =  utf8_decode($value);
    }

    public function getespecieAttribute($value)
    {
      return utf8_encode($value);
    }
    public function setespecieAttribute($value)
    {
      $this->attributes['especie'] =  utf8_decode($value);
    }

    public function getestorageurlAttribute($value)
    {
      return utf8_encode($value);
    }
    public function setstorageurlAttribute($value)
    {
      $this->attributes['storageurl'] =  utf8_decode($value);
    }

    public function getbaixanfemsgAttribute($value)
    {
      return utf8_encode($value);
    }
    public function setbaixanfemsgAttribute($value)
    {
      $this->attributes['baixanfemsg'] =  utf8_decode($value);
    }


    public function getultimoerroAttribute($value)
    {
        if ($this->baixanfestatus === 0) {
            return 'XML da nota fiscal ainda não foi baixado.';
        } else if ($this->baixanfestatus === 2) {
            return $this->baixanfemsg ;
        } else if ($this->baixanfestatus === 1) {
            if ($this->xmlprocessado !== 1) return 'XML da nota fiscal ainda não foi processado.';
            if ($this->coletaavulsa === 1) {
                return $this->coletaavulsaerrormsg;
            }
        }
    }
}
