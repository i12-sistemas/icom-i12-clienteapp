<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CargaEntrada extends Model
{
    protected $table = 'cargaentrada';
    protected $dates = ['dhentrada', 'created_at', 'updated_at'];
    public $timestamps = true;

    public function export($complete = true)
    {
        $dados = $this->toArray();
        $dados['dhentrada'] = $this->dhentrada ? $this->dhentrada->format('Y-m-d H:i:00') : null;
        $dados['created_at'] = $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null;
        $dados['updated_at'] = $this->updated_at ? $this->updated_at->format('Y-m-d H:i:s') : null;

        $dados['unidadeentrada'] = $this->unidadeentrada ? $this->unidadeentrada->toSimple() : null;
        if ($this->tipo == '1') {
            $dados['motorista'] = $this->motorista ? $this->motorista->exportsmall() : null;
            $dados['veiculo'] = $this->veiculo ? $this->veiculo->exportsmall() : null;
        }

        if ($complete) {
            $dados['created_usuario'] = $this->created_usuario ? $this->created_usuario->toSimple() : null;
            $dados['updated_usuario'] = $this->updated_usuario ? $this->updated_usuario->toSimple() : null;

            $aItens = [];
            foreach ($this->itens as $e) {
                $aItens[] = $e->export();
            }
            $dados['itens'] = $aItens;
        }

        unset($dados['unidadeentradaid']);
        unset($dados['motoristaid']);
        unset($dados['veiculoid']);
        unset($dados['useridcreated']);
        unset($dados['useridupdated']);

        return $dados;
    }


    public function toSimple()
    {
        $dados = $this->toArray();
        $dados['dhentrada'] = $this->dhentrada ? $this->dhentrada->format('Y-m-d H:i:00') : null;
        $dados['created_at'] = $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null;
        $dados['updated_at'] = $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null;
        $dados['unidadeentrada'] = $this->unidadeentrada ? $this->unidadeentrada->toSimple() : null;
        unset($dados['unidadeentradaid']);
        unset($dados['motoristaid']);
        unset($dados['veiculoid']);
        unset($dados['useridcreated']);
        unset($dados['useridupdated']);
        return $dados;
    }

    public function itens()
    {
        return $this->hasMany(CargaEntradaItem::class, 'cargaentradaid', 'id');
    }

    public function created_usuario()
    {
        return $this->hasOne(Usuario::class, 'id', 'useridcreated');
    }

    public function updated_usuario()
    {
        return $this->hasOne(Usuario::class, 'id', 'useridupdated');
    }

    public function unidadeentrada()
    {
        return $this->hasOne(Unidade::class, 'id', 'unidadeentradaid')->with('cidade');
    }

    public function motorista()
    {
        return $this->hasOne(Motorista::class, 'id', 'motoristaid');
    }

    public function veiculo()
    {
        return $this->hasOne(Veiculo::class, 'id', 'veiculoid');
    }


    public function geterromsgAttribute($value)
    {
      return utf8_encode($value);
    }
    public function seterromsgAttribute($value)
    {
      $this->attributes['erromsg'] =  utf8_decode($value);
    }

    public function totaliza()
    {
        $volqtde = $this->itens()->sum('nfevol');
        $peso = $this->itens()->sum('nfepeso');
        $erroqtde = $this->itens()->whereRaw('ifnull(errors,"")<>""')->count();
        $qtdeprocessadomanual = $this->itens()->where('tipoprocessamento', '=', '2')->count();

        $msg = [];
        $erros = $this->itens()->whereRaw('ifnull(errors,"")<>""')->get();
        foreach ($erros as $key => $row) {
            $list = json_decode($row->errors);
            foreach ($list as $e) {
                if (!in_array($e, $msg)) $msg[] = $e;
            }
        }

        $qtdeconferida = 0;
        foreach ($this->itens as $item) {
            foreach ($item->etiquetas as $etiqueta) {
                if ($etiqueta->conferidoentrada === 1) $qtdeconferida += 1;
            }
        }
        $percconferido = 0;
        if ($volqtde > 0) {
            $percconferido = round(($qtdeconferida / $volqtde) * 100, 2);
        }


        $this->attributes['conferidoprogresso'] =  $percconferido;
        $this->attributes['conferidoqtde'] =  $qtdeconferida;
        $this->attributes['volqtde'] =  $volqtde ? $volqtde : 0;
        $this->attributes['peso'] =  $peso ? $peso : 0;
        $this->attributes['erroqtde'] =  $erroqtde ? $erroqtde : 0;
        $this->attributes['editadomanualmente'] =  $qtdeprocessadomanual>0 ? 1 : 0;
        $this->attributes['erromsg'] =  count($msg) > 0 ? json_encode($msg) : null;

    }

}
