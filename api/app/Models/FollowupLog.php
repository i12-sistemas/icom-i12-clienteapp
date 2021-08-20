<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class FollowupLog extends Model
{
    protected $table = 'followup_log';
    protected $dates = ['created_at', 'datasolicitacao', 'dataagendamentocoleta', 'dataconfirmacao', 'datacoleta', 'datapromessa', 'datahora_followup'];
    public $timestamps = false;

    public function export($complete = false)
    {
        $dados = $this->toArray();
        $dados['created_at'] = $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null;
        $dados['datasolicitacao'] = $this->datasolicitacao ? $this->datasolicitacao->format('Y-m-d') : null;
        $dados['dataagendamentocoleta'] = $this->dataagendamentocoleta ? $this->dataagendamentocoleta->format('Y-m-d') : null;
        $dados['dataconfirmacao'] = $this->dataconfirmacao ? $this->dataconfirmacao->format('Y-m-d') : null;
        $dados['datacoleta'] = $this->datacoleta ? $this->datacoleta->format('Y-m-d') : null;
        $dados['datapromessa'] = $this->datapromessa ? $this->datapromessa->format('Y-m-d') : null;
        $dados['datahora_followup'] = $this->datahora_followup ? $this->datahora_followup->format('Y-m-d H:i:s') : null;


        $dados['erroagenda'] = $this->erroagenda ? $this->erroagenda->toArray() : null;
        unset($dados['erroagendaid']);

        $dados['errocoleta'] = $this->errocoleta ? $this->errocoleta->toArray() : null;
        unset($dados['errocoletaid']);

        $dados['errodtpromessa'] = $this->errodtpromessa ? $this->errodtpromessa->toArray() : null;
        unset($dados['errodtpromessaid']);

        if ($this->created_usuarioid > 0) $dados['created_usuario'] = $this->created_usuario->toObject(false);
        unset($dados['created_usuarioid']);

        return $dados;
    }

    public function created_usuario()
    {
        return $this->hasOne(Usuario::class, 'id', 'created_usuarioid');
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

    public function setobservacaoAttribute($value)
    {
        $this->attributes['observacao'] =  Str::limit(utf8_decode($value),255, '');
    }
    public function getobservacaoAttribute($value)
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
}
