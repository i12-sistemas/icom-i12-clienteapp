<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CargaEntradaItem extends Model
{
    protected $table = 'cargaentradaitem';
    protected $dates = ['dhentrada'];
    public $timestamps = true;
    public $hidden = ['erros'];

    public function export($complete = true, $ignoreEtiquetas = false)
    {
        $dados = $this->toArray();
        $dados['created_at'] = $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null;
        $dados['updated_at'] = $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null;
        if ($complete) {
            if ($this->tipoprocessamento == '2') $dados['manual_usuario'] = $this->manual_usuario ? $this->manual_usuario->toSimple() : null;
        }

        if (!$this->errors) unset($dados['errors']);
        if (trim($this->errors) === '') unset($dados['errors']);

        if (!$ignoreEtiquetas) {
            $aEtiquetas = [];
            foreach ($this->etiquetas as $e) {
                $aEtiquetas[] = $e->export(false);
            }
            $dados['etiquetas'] = $aEtiquetas;
        }

        unset($dados['manualuserid']);
        return $dados;
    }

    public function etiquetas()
    {
        return $this->hasMany(Etiquetas::class, 'cargaentradaitem', 'id');
    }


    public function manual_usuario()
    {
        return $this->hasOne(Usuario::class, 'id', 'manualuserid');
    }

    public function coletanota()
    {
        return $this->hasOne(ColetasNota::class, 'id', 'coletanotaid');
    }


    public function coleta()
    {
        return $this->hasOne(Coletas::class, 'id', 'coletaid');
    }

    public function cargaentrada()
    {
        return $this->hasOne(CargaEntrada::class, 'id', 'cargaentradaid');
    }

    public function getnfecnpjAttribute($value)
    {
      return utf8_encode($value);
    }
    public function setnfecnpjAttribute($value)
    {
      $this->attributes['nfecnpj'] =  utf8_decode($value);
    }

    public function getnfechaveAttribute($value)
    {
      return utf8_encode($value);
    }
    public function setnfechaveAttribute($value)
    {
      $this->attributes['nfechave'] =  utf8_decode($value);
    }

    public function geterrorsAttribute($value)
    {
      return utf8_encode($value);
    }

    public function seterrorsAttribute($value)
    {
        $s = trim($value);

        if ($s === '') {
            $this->attributes['errors'] = null;
        }  else {
            $this->attributes['errors'] = utf8_decode($s);

        }
    }

    public function checkErros()
    {
        $errors = [];
        if (!($this->nfevol > 0)) $errors[] = 'Nenhum volume identificado';
        if (!($this->nfepeso > 0)) $errors[] = 'Nenhum peso identificado';
        if (!($this->nfenumero > 0)) $errors[] = 'Número da nota não foi identificado';
        if (!$this->coletaid) $errors[] = 'Coleta não identificada';
        $qtdeEtiquetas = $this->etiquetas->count();
        if (($this->nfevol > 0) && ($this->nfevol !== $qtdeEtiquetas) && ($qtdeEtiquetas > 0))
            $errors[] = 'Quantidade de volume difere da quantidade de etiqueta gerada';

        if (($this->nfevol > 0) && ($qtdeEtiquetas === 0))
            $errors[] = 'Pendente gerar etiqueta';

        $this->attributes['errors'] = count($errors) > 0 ? json_encode($errors) : null;
    }

}
