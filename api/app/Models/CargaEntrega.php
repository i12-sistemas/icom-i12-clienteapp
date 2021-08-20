<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Etiquetas;
use Illuminate\Support\Facades\DB;

class CargaEntrega extends Model
{
    protected $table = 'cargaentrega';
    protected $dates = ['saidadh', 'entregaultimadh', 'created_at', 'updated_at'];
    public $timestamps = true;

    public static function boot()
    {
      parent::boot();

      self::creating(function ($model) {
        $model->senha = mb_strtoupper(createRandomVal(6));
      });
    }

    public function export($complete = true)
    {
        $dados = $this->toArray();
        $dados['entregaultimadh'] = $this->entregaultimadh ? $this->entregaultimadh->format('Y-m-d H:i:s') : null;
        $dados['saidadh'] = $this->saidadh ? $this->saidadh->format('Y-m-d H:i:s') : null;
        $dados['created_at'] = $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null;
        $dados['updated_at'] = $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null;

        $dados['unidadesaida'] = $this->unidadesaida ? $this->unidadesaida->toSimple() : null;
        $dados['motorista'] = $this->motorista ? $this->motorista->exportsmall() : null;
        $dados['veiculo'] = $this->veiculo ? $this->veiculo->toObject(false) : null;

        if ($this->status !== '1') $dados['saida_usuario'] = $this->saida_usuario ? $this->saida_usuario->toSimple() : null;

        if ($complete) {
            $dados['created_usuario'] = $this->created_usuario ? $this->created_usuario->toSimple() : null;
            $dados['updated_usuario'] = $this->updated_usuario ? $this->updated_usuario->toSimple() : null;

            $aItens = [];
            foreach ($this->itens as $e) {
                $aItens[] = $e->export(true);
            }
            $dados['itens'] = $aItens;
        } else {
            unset($dados['senha']);
        }

        unset($dados['unidadesaidaid']);
        unset($dados['saidauserid']);
        unset($dados['motoristaid']);
        unset($dados['veiculoid']);
        unset($dados['useridcreated']);
        unset($dados['useridupdated']);

        return $dados;
    }

    public function itens()
    {
        return $this->hasMany(CargaEntregaItem::class, 'cargaentregaid', 'id')
                        ->join('etiquetas', 'cargaentregaitem.etiquetaean13', '=', 'etiquetas.ean13')
                        ->orderBy('etiquetas.cargaentradaitem')
                        ->orderBy('etiquetas.volnum')
                        ->orderBy('etiquetas.voltotal')
                        ->with('etiqueta');
    }

    public function created_usuario()
    {
        return $this->hasOne(Usuario::class, 'id', 'useridcreated');
    }

    public function updated_usuario()
    {
        return $this->hasOne(Usuario::class, 'id', 'useridupdated');
    }


    public function saida_usuario()
    {
        return $this->hasOne(Usuario::class, 'id', 'saidauserid');
    }


    public function unidadesaida()
    {
        return $this->hasOne(Unidade::class, 'id', 'unidadesaidaid')->with('cidade');
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
        $volqtde = 0;
        $peso = 0;
        $etiquetasAssociadas = $this->itens()->with('etiqueta')->get();
        foreach ($etiquetasAssociadas as $key => $item) {
            $peso = $peso + $item->etiqueta->pesovol;
        }

        $grupo = $this->itens()->with('etiqueta')
                        ->groupBy('etiquetas.cargaentradaitem')
                        ->groupBy('etiquetas.voltotal')
                        ->orderBy('etiquetas.cargaentradaitem')
                        ->orderBy('etiquetas.volnum')
                        ->orderBy('etiquetas.voltotal')
                        ->get();

        $erros = [];
        foreach ($grupo as $key => $eti) {
            for ($i=$eti->voltotal; $i >= 1; $i--) {
                $volqtde += 1;
                $etiquetaEncontrada = $etiquetasAssociadas
                                            ->where('cargaentradaitem', '=', $eti->cargaentradaitem)
                                            ->where('voltotal', '=', $eti->voltotal)
                                            ->where('volnum', '=', $i);
                if ($etiquetaEncontrada->isEmpty())  {
                    $etiFaltando = Etiquetas::where('cargaentradaitem', '=', $eti->cargaentradaitem)->where('volnum', '=', $i)->where('voltotal', '=', $eti->voltotal)->first();
                    $erros[] = [
                        'msg' => 'Faltando etiqueta volume ' . $i . '/' .  $eti->voltotal . ' - Etiqueta ref. ' . $eti->ean13,
                        'cargaentradaitemid' => $etiFaltando->cargaentradaitem,
                        'etiqueta' => [
                            'ean13' => $etiFaltando->ean13,
                            'volnum' => $etiFaltando->volnum,
                            'voltotal' => $etiFaltando->voltotal,
                        ],
                    ];
                }
            }
        }

        $itens = $this->itens;
        foreach ($itens as $key => $item) {
            $cteinvalido = [];
            if (!testaChaveNFe($item->ctechave)) $cteinvalido[] = 'Chave do CT-e inválida ou não informada';
            if ($item->ctecnpj ?  strlen($item->ctecnpj) !== 14 : true) $cteinvalido[] = 'CNPJ do emissor do CT-e inválido';
            if ($item->ctenumero ?  !(intval($item->ctenumero) > 0) : true) $cteinvalido[] = 'Número do CT-e inválido';

            if (count($cteinvalido) > 0) {
                $erros[] = [
                    'id' => $item->id,
                    'msg' => 'Item ' . ($key+1) . ' - ' . implode(', ', $cteinvalido),
                    'etiqueta' => [
                        'ean13' => $item->etiqueta->ean13,
                        'volnum' => $item->etiqueta->volnum,
                        'voltotal' => $item->etiqueta->voltotal,
                    ],
                ];
            };
        }
        $this->attributes['volqtde'] =  $volqtde;
        $this->attributes['peso'] =  $peso;
        if (( $erros ? count($erros) : 0 ) > 0) {
          $this->attributes['erroqtde'] = count($erros);
          $this->attributes['erromsg'] =  json_encode($erros);
        } else {
          $this->attributes['erroqtde'] = 0;
          $this->attributes['erromsg'] =  null;
        }


        // 2=Por item
        $operacao = null;
        $qtdeitem = $this->itens->count();
        $qtdeentregue = 0;
        $entregapercentual = 0;
        if (($this->entregatipo === '2') || ($this->entregatipo === '1')) {
            $entregaultimadh = $this->itens()->where('cargaentregaitem.entregue', '=', 1)->max(DB::raw('cargaentregaitem.entreguedh'));
            $this->attributes['entregaultimadh'] =  $entregaultimadh;

            $qtdeentregue = $this->itens()->where('entregue', '=', 1)->count();
            if ($qtdeitem !== 0) {
                $entregapercentual = (($qtdeentregue / $qtdeitem) * 100);
            }

            $operacao = 'A';
            $qtdeentreguemanual = $this->itens()->where('entregue', '=', 1)->where('entregueoperacao', '=', 'M')->count();
            if ($qtdeentreguemanual > 0) {
                $operacao = 'M';
            }
        }

        $this->attributes['entregaoperacao'] =  $operacao;
        $this->attributes['entregaqtdeitem'] =  $qtdeentregue;
        $this->attributes['entregapercentual'] =  $entregapercentual;

        if (($qtdeentregue > 0) && ($entregapercentual === 100)) {
            $this->attributes['status'] = '4'; //4=Entregue
        }
    }

    public function qrcodetext()
    {
        $hash = md5($this->id . $this->created_at->format('Y-m-d H:i:s') . '_&&' . $this->senha);
        $hash = bcrypt($hash);
        $valor = [
            'hash' =>  $hash,
            'cargaid' => $this->id,
            'senha' => $this->senha,
            'created_at' =>  $this->created_at->format('Y-m-d H:i:s'),
            'unidadeid' => $this->unidadesaidaid,
            'motoristaid' => $this->motoristaid,
            'veiculo' => $this->veiculo ? $this->veiculo->placa : null
        ];

        return  json_encode($valor);
    }


    public function qrcode()
    {
        return  \DNS2D::getBarcodeHTML( $this->qrcodetext(), 'QRCODE');
    }
}
