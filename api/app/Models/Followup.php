<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

use App\Models\FollowupLog;
use Exception;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class Followup extends Model
{
    protected $table = 'followup';
    protected $dates = ['datasolicitacao', 'datapromessa', 'dataconfirmacao', 'dhimportacao', 'aprovacaorc',
                        'datanecessidaderc', 'criacaooc', 'aprovacaooc', 'dataliberacao', 'dataultimaentrada',
                        'dataagendamentocoleta', 'datacoleta', 'datahora_followup', 'datahoralancamento'];
    public $timestamps = false;


    public function export($complete = false)
    {
        $dados = $this->toArray();
        $dados['datahoralancamento'] = $this->datahoralancamento ? $this->datahoralancamento->format('Y-m-d H:i:s') : null;
        $dados['dhimportacao'] = $this->dhimportacao ? $this->dhimportacao->format('Y-m-d H:i:s') : null;
        $dados['datahora_followup'] = $this->datahora_followup ? $this->datahora_followup->format('Y-m-d H:i:s') : null;
        $dados['datasolicitacao'] = $this->datasolicitacao ? $this->datasolicitacao->format('Y-m-d') : null;
        $dados['criacaooc'] = $this->criacaooc ? $this->criacaooc->format('Y-m-d') : null;
        $dados['aprovacaooc'] = $this->aprovacaooc ? $this->aprovacaooc->format('Y-m-d') : null;
        $dados['aprovacaorc'] = $this->aprovacaorc ? $this->aprovacaorc->format('Y-m-d') : null;
        $dados['dataagendamentocoleta'] = $this->dataagendamentocoleta ? $this->dataagendamentocoleta->format('Y-m-d') : null;
        $dados['datapromessa'] = $this->datapromessa ? $this->datapromessa->format('Y-m-d') : null;
        $dados['datacoleta'] = $this->datacoleta ? $this->datacoleta->format('Y-m-d') : null;
        $dados['datanecessidaderc'] = $this->datanecessidaderc ? $this->datanecessidaderc->format('Y-m-d') : null;
        $dados['dataultimaentrada'] = $this->dataultimaentrada ? $this->dataultimaentrada->format('Y-m-d') : null;
        // $dados['dhbaixa'] = $this->dhbaixa ? $this->dhbaixa->format('Y-m-d H:i:s') : null;

        // $dados['endcoleta_cidade'] = $this->coletacidade ? $this->coletacidade->toSmallObject() : null;

        if ($this->fornecedorid > 0) $dados['fornecedor'] = $this->fornecedor ? $this->fornecedor->toSmallObject() : null;

        $dados['cliente'] = $this->cliente ? $this->cliente->toSmallObject() : null;
        unset($dados['clienteid']);

        $dados['erroagenda'] = $this->erroagenda ? $this->erroagenda->toArray() : null;
        unset($dados['erroagendaid']);

        $dados['errocoleta'] = $this->errocoleta ? $this->errocoleta->toArray() : null;
        unset($dados['errocoletaid']);

        $dados['errodtpromessa'] = $this->errodtpromessa ? $this->errodtpromessa->toArray() : null;
        unset($dados['errodtpromessaid']);

        if ($this->updated_usuarioid > 0) $dados['updated_usuario'] = $this->updated_usuario->toObject(false);
        unset($dados['updated_usuarioid']);
        // unset($dados['eventos']);

        return $dados;
    }

    public function registerLog($tipoorigem, $usuarioid, $controlaTransacao = true)
    {

        if (!$this->id) throw new Exception('Nenhum fup encontrado');
        if (!($this->id > 0)) throw new Exception('Nenhum fup encontrado');

        try {

            if ($controlaTransacao) DB::beginTransaction();

            // datapromessa
            // itemnumerolinhapedido
            // ordemcompra

            $row = new FollowupLog();
            $row->followupid = $this->id;
            $row->created_usuarioid = $usuarioid;
            $row->created_at = Carbon::now();
            $row->datasolicitacao = $this->datasolicitacao;
            $row->dataagendamentocoleta = $this->dataagendamentocoleta;
            $row->erroagendastatus = $this->erroagendastatus ? $this->erroagendastatus : '0';
            $row->erroagendaid = $this->erroagendaid;
            $row->iniciofollowup = $this->iniciofollowup ? $this->iniciofollowup : '0';
            $row->errodtpromessaid = $this->errodtpromessaid;
            $row->errodtpromessastatus = $this->errodtpromessastatus ? $this->errodtpromessastatus : '0';
            $row->observacao = $this->observacao;
            $row->dataconfirmacao = $this->dataconfirmacao;
            $row->statusconfirmacaocoleta = $this->statusconfirmacaocoleta ? $this->statusconfirmacaocoleta : '0';
            $row->coletaid = $this->coletaid;
            $row->notafiscal = $this->notafiscal;
            $row->datacoleta = $this->datacoleta;
            $row->errocoletastatus = $this->errocoletastatus ? $this->errocoletastatus : '0';
            $row->errocoletaid = $this->errocoletaid;
            $row->datapromessa = $this->datapromessa;
            $row->vlrunitario = $this->vlrunitario;
            $row->totallinhaoc = $this->totallinhaoc;
            $row->itemnumerolinhapedido = $this->itemnumerolinhapedido;
            $row->qtdesolicitada = $this->qtdesolicitada ? $this->qtdesolicitada : 0;
            $row->qtderecebida = $this->qtderecebida ? $this->qtderecebida : 0;
            $row->qtdedevida = $this->qtdedevida ? $this->qtdedevida : 0;
            $row->ordemcompra = $this->ordemcompra;
            $row->ordemcompradig = $this->ordemcompradig;
            $row->datahora_followup = $this->datahora_followup;
            $row->tipoorigem = $tipoorigem;
            $row->save();

            if ($controlaTransacao) DB::commit();

            return true;

        } catch (\Throwable $th) {
            if ($controlaTransacao) DB::rollBack();
            throw new Exception('Erro ao salvar log - Erro: ' . $th->getMessage());
        }
    }

    public function updated_usuario()
    {
        return $this->hasOne(Usuario::class, 'id', 'updated_usuarioid');
    }

    public function cliente()
    {
        return $this->hasOne(Cliente::class, 'id', 'clienteid')->with('cidade');
    }

    public function fornecedor()
    {
        return $this->hasOne(Cliente::class, 'id', 'fornecedorid')->with('cidade');
    }

    public function erroagenda()
    {
        return $this->hasOne(FollowupErros::class, 'id', 'erroagendaid');
    }

    public function errocoleta()
    {
        return $this->hasOne(FollowupErros::class, 'id', 'errocoletaid');
    }

    public function errodtpromessa()
    {
        return $this->hasOne(FollowupErros::class, 'id', 'errodtpromessaid');
    }

    public function coleta()
    {
        return $this->hasOne(Coletas::class, 'id', 'coletaid');
    }


    public function setforneccidadeAttribute($value)
    {
        $this->attributes['forneccidade'] =  Str::limit(utf8_decode($value),100, '');
    }
    public function getforneccidadeAttribute($value)
    {
      return utf8_encode($value);
    }

    public function setcontatoAttribute($value)
    {
        $this->attributes['contato'] =  Str::limit(utf8_decode($value),100, '');
    }
    public function getcontatoAttribute($value)
    {
      return utf8_encode($value);
    }

    public function setemailAttribute($value)
    {
        $this->attributes['email'] =  Str::limit(utf8_decode($value),100, '');
    }
    public function getemailAttribute($value)
    {
      return utf8_encode($value);
    }

    public function setforneccnpjAttribute($value)
    {
        $this->attributes['forneccnpj'] =  Str::limit(utf8_decode($value),14, '');
    }
    public function getforneccnpjAttribute($value)
    {
      return utf8_encode($value);
    }

    public function setfornectelefoneAttribute($value)
    {
        $this->attributes['fornectelefone'] =  Str::limit(utf8_decode($value),15, '');
    }
    public function getfornectelefoneAttribute($value)
    {
      return utf8_encode($value);
    }

    public function setcondpagtoAttribute($value)
    {
        $this->attributes['condpagto'] =  Str::limit(utf8_decode($value), 15, '');
    }
    public function getcondpagtoAttribute($value)
    {
      return utf8_encode($value);
    }

    public function setfornecufAttribute($value)
    {
        $this->attributes['fornecuf'] =  Str::limit(utf8_decode($value), 2, '');
    }
    public function getfornecufAttribute($value)
    {
      return utf8_encode($value);
    }

    public function setobservacaoAttribute($value)
    {
        $this->attributes['observacao'] =  Str::limit(utf8_decode($value),255, '');
    }
    public function getobservacaoAttribute($value)
    {
      return utf8_encode($value);
    }

    public function setcompradelegadaAttribute($value)
    {
        $this->attributes['compradelegada'] = Str::limit(utf8_decode($value),3, '');
    }
    public function getcompradelegadaAttribute($value)
    {
      return utf8_encode($value);
    }

    public function settipofreteAttribute($value)
    {
        $this->attributes['tipofrete'] =  Str::limit(utf8_decode($value),3, '');
    }
    public function gettipofreteAttribute($value)
    {
      return utf8_encode($value);
    }

    public function setrequisicaoAttribute($value)
    {
        $this->attributes['requisicao'] =  Str::limit(utf8_decode($value),300, '');
    }
    public function getrequisicaoAttribute($value)
    {
      return utf8_encode($value);
    }

    public function setcompradorAttribute($value)
    {
        $this->attributes['comprador'] =  Str::limit(utf8_decode($value),300, '');
    }
    public function getcompradorAttribute($value)
    {
      return utf8_encode($value);
    }

    public function setfornecrazaoAttribute($value)
    {
        $this->attributes['fornecrazao'] =  Str::limit(utf8_decode($value),300, '');
    }
    public function getfornecrazaoAttribute($value)
    {
      return utf8_encode($value);
    }

    public function setitemdescricaoAttribute($value)
    {
        $this->attributes['itemdescricao'] =  Str::limit(utf8_decode($value),300, '');
    }
    public function getitemdescricaoAttribute($value)
    {
      return utf8_encode($value);
    }

    public function setmoedaAttribute($value)
    {
        $this->attributes['moeda'] =  Str::limit(utf8_decode($value),5, '');
    }
    public function getmoedaAttribute($value)
    {
      return utf8_encode($value);
    }

    public function setitemidAttribute($value)
    {
        $this->attributes['itemid'] = Str::limit(utf8_decode($value),50, '');
    }
    public function getitemidAttribute($value)
    {
      return utf8_encode($value);
    }

    public function settipoocAttribute($value)
    {
        $this->attributes['tipooc'] =  Str::limit(utf8_decode($value),50, '');
    }
    public function gettipoocAttribute($value)
    {
      return utf8_encode($value);
    }

    public function setstatusocAttribute($value)
    {
        $this->attributes['statusoc'] =  Str::limit(utf8_decode($value),50, '');
    }
    public function getstatusocAttribute($value)
    {
      return utf8_encode($value);
    }

    public function setstatusliberacaoAttribute($value)
    {
        $this->attributes['statusliberacao'] =  Str::limit(utf8_decode($value),50, '');
    }
    public function getstatusliberacaoAttribute($value)
    {
      return utf8_encode($value);
    }

    public function setsituacaolinhaAttribute($value)
    {
        $this->attributes['situacaolinha'] =  Str::limit(utf8_decode($value),50, '');
    }
    public function getsituacaolinhaAttribute($value)
    {
      return utf8_encode($value);
    }

    public function setcompradoracordoAttribute($value)
    {
        $this->attributes['compradoracordo'] =  Str::limit(utf8_decode($value),50, '');
    }
    public function getcompradoracordoAttribute($value)
    {
      return utf8_encode($value);
    }

    public function setgrupoAttribute($value)
    {
        $this->attributes['grupo'] =  Str::limit(utf8_decode($value),255, '');
    }
    public function getgrupoAttribute($value)
    {
      return utf8_encode($value);
    }

    public function setfamiliaAttribute($value)
    {
        $this->attributes['familia'] =  Str::limit(utf8_decode($value),255, '');
    }
    public function getfamiliaAttribute($value)
    {
      return utf8_encode($value);
    }

    public function setsubfamiliaAttribute($value)
    {
        $this->attributes['subfamilia'] = Str::limit(utf8_decode($value),255, '');
    }
    public function getsubfamiliaAttribute($value)
    {
      return utf8_encode($value);
    }

    public function setudmAttribute($value)
    {
        $this->attributes['udm'] =  Str::limit(utf8_decode($value),50, '');
    }
    public function getudmAttribute($value)
    {
      return utf8_encode($value);
    }

    public function setnotafiscalAttribute($value)
    {
        $this->attributes['notafiscal'] = Str::limit(utf8_decode($value), 50, '');
    }
    public function getnotafiscalAttribute($value)
    {
      return utf8_encode($value);
    }

    public function setnormalurgenteAttribute($value)
    {
        $this->attributes['normalurgente'] =  Str::limit(utf8_decode($value),7, '');
    }
    public function getnormalurgenteAttribute($value)
    {
      return utf8_encode($value);
    }
}
