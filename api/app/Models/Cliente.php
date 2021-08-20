<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    protected $table = 'cliente';

    protected $dates = ['segqui_hr1_i', 'segqui_hr1_f', 'segqui_hr2_i', 'segqui_hr2_f',
                       'sex_hr1_i', 'sex_hr1_f', 'sex_hr2_i', 'sex_hr2_f',
                       'portaria_hr1_i', 'portaria_hr1_f', 'portaria_hr2_i', 'portaria_hr2_f'
                    ];

    public function toObject($showCompact = false)
    {
        $dados = $this->toArray();
        $dados['segqui_hr1_i'] = $this->segqui_hr1_i ? $this->segqui_hr1_i->format('H:i') : null;
        $dados['segqui_hr1_f'] = $this->segqui_hr1_f ? $this->segqui_hr1_f->format('H:i') : null;
        $dados['segqui_hr2_i'] = $this->segqui_hr2_i ? $this->segqui_hr2_i->format('H:i') : null;
        $dados['segqui_hr2_f'] = $this->segqui_hr2_f ? $this->segqui_hr2_f->format('H:i') : null;

        $dados['sex_hr1_i'] = $this->sex_hr1_i ? $this->sex_hr1_i->format('H:i') : null;
        $dados['sex_hr1_f'] = $this->sex_hr1_f ? $this->sex_hr1_f->format('H:i') : null;
        $dados['sex_hr2_i'] = $this->sex_hr2_i ? $this->sex_hr2_i->format('H:i') : null;
        $dados['sex_hr2_f'] = $this->sex_hr2_f ? $this->sex_hr2_f->format('H:i') : null;

        $dados['portaria_hr1_i'] = $this->portaria_hr1_i ? $this->portaria_hr1_i->format('H:i') : null;
        $dados['portaria_hr1_f'] = $this->portaria_hr1_f ? $this->portaria_hr1_f->format('H:i') : null;
        $dados['portaria_hr2_i'] = $this->portaria_hr2_i ? $this->portaria_hr2_i->format('H:i') : null;
        $dados['portaria_hr2_f'] = $this->portaria_hr2_f ? $this->portaria_hr2_f->format('H:i') : null;

        $aEmails = [];
        foreach ($this->emails as $e) {
            $aE = $e->toObject($showCompact);
            unset($aE['pivot']);
            $aEmails[] = $aE;
        }
        unset($dados['emails']);
        $dados['emails'] = $aEmails;

        $dados['cidade'] = $this->cidade ? $this->cidade->toSmallObject() : null;
        unset($dados['cidadeid']);
        $dados['created_usuario'] = $this->created_usuario ? $this->created_usuario->toObject(false) : null;
        $dados['updated_usuario'] = $this->updated_usuario ? $this->updated_usuario->toObject(false) : null;
        unset($dados['created_usuarioid']);
        unset($dados['updated_usuarioid']);

        if ($showCompact) {
            unset($dados['cnpjmemo']);
        }
        return $dados;
    }

    public function toSmallObject()
    {
        $dados = [
            'id'    => $this->id,
            'ativo'    => $this->ativo,
            'fantasia'    => $this->fantasia,
            'razaosocial'    => $this->razaosocial,
            'fantasia_followup'    => $this->fantasia_followup,
            'followupid'    => $this->followupid,
            'logradouro'    => $this->logradouro,
            'endereco'    => $this->endereco,
            'numero'    => $this->numero,
            'complemento'    => $this->complemento,
            'bairro'    => $this->bairro,
            'cep'    => $this->cep,
            'cidade'    => $this->cidade ? $this->cidade->toSmallObject() : null,
            'cnpj'    => $this->cnpj,
            'fone1'    => $this->fone1,
            'fone2'    => $this->fone2
        ];
        $dados['segqui_hr1_i'] = $this->segqui_hr1_i ? $this->segqui_hr1_i->format('H:i') : null;
        $dados['segqui_hr1_f'] = $this->segqui_hr1_f ? $this->segqui_hr1_f->format('H:i') : null;
        $dados['segqui_hr2_i'] = $this->segqui_hr2_i ? $this->segqui_hr2_i->format('H:i') : null;
        $dados['segqui_hr2_f'] = $this->segqui_hr2_f ? $this->segqui_hr2_f->format('H:i') : null;

        $dados['sex_hr1_i'] = $this->sex_hr1_i ? $this->sex_hr1_i->format('H:i') : null;
        $dados['sex_hr1_f'] = $this->sex_hr1_f ? $this->sex_hr1_f->format('H:i') : null;
        $dados['sex_hr2_i'] = $this->sex_hr2_i ? $this->sex_hr2_i->format('H:i') : null;
        $dados['sex_hr2_f'] = $this->sex_hr2_f ? $this->sex_hr2_f->format('H:i') : null;

        $dados['portaria_hr1_i'] = $this->portaria_hr1_i ? $this->portaria_hr1_i->format('H:i') : null;
        $dados['portaria_hr1_f'] = $this->portaria_hr1_f ? $this->portaria_hr1_f->format('H:i') : null;
        $dados['portaria_hr2_i'] = $this->portaria_hr2_i ? $this->portaria_hr2_i->format('H:i') : null;
        $dados['portaria_hr2_f'] = $this->portaria_hr2_f ? $this->portaria_hr2_f->format('H:i') : null;


        return $dados;
    }

    public function emails()
    {
        return $this->belongsToMany(Emails::class, 'cliente_email', 'clienteid', 'emailid')->orderBy('nome','asc')->orderBy('email','asc');
    }


    public function clienteusuario()
    {
        return $this->hasMany(ClienteUsuario::class, 'clienteid', 'id')->orderBy('nome', 'asc');
    }


    public function created_usuario()
    {
        return $this->hasOne(Usuario::class, 'id', 'created_usuarioid');
    }

    public function updated_usuario()
    {
        return $this->hasOne(Usuario::class, 'id', 'updated_usuarioid');
    }

    public function cidade()
    {
        return $this->hasOne(Cidades::class, 'id', 'cidadeid')->with('regiao');
    }


    public function getcnpjAttribute($value)
    {
      return utf8_encode($value);
    }
    public function setcnpjAttribute($value)
    {
      $this->attributes['cnpj'] =  utf8_decode($value);
    }

    public function getcnpjmemoAttribute($value)
    {
      return utf8_encode($value);
    }
    public function setcnpjmemoAttribute($value)
    {
      $this->attributes['cnpjmemo'] =  utf8_decode($value);
    }

    public function getrazaosocialAttribute($value)
    {
      return utf8_encode($value);
    }
    public function setrazaosocialAttribute($value)
    {
      $this->attributes['razaosocial'] =  utf8_decode($value);
    }

    public function getfantasiaAttribute($value)
    {
      return utf8_encode($value);
    }
    public function setfantasiaAttribute($value)
    {
      $this->attributes['fantasia'] =  utf8_decode($value);
    }

    public function getfantasiaFollowupAttribute($value)
    {
      return utf8_encode($value);
    }
    public function setfantasiaFollowupAttribute($value)
    {
      $this->attributes['fantasia_followup'] =  utf8_decode($value);
    }

    public function getfone1Attribute($value)
    {
      return utf8_encode($value);
    }
    public function setfone1Attribute($value)
    {
      $this->attributes['fone1'] =  utf8_decode($value);
    }

    public function getfone2Attribute($value)
    {
      return utf8_encode($value);
    }
    public function setfone2Attribute($value)
    {
      $this->attributes['fone2'] =  utf8_decode($value);
    }

    public function getobsAttribute($value)
    {
      return utf8_encode($value);
    }
    public function setobsAttribute($value)
    {
      $this->attributes['obs'] =  utf8_decode($value);
    }

    public function getlogradouroAttribute($value)
    {
      return utf8_encode($value);
    }
    public function setlogradouroAttribute($value)
    {
      $this->attributes['logradouro'] =  utf8_decode($value);
    }




    public function getenderecoenumeroAttribute($value) {
        //não usar utf8_encode pois apresenta erro na view de PDF
        $end = $this->logradouro . ' ' . $this->endereco . ', ' . $this->numero;
        return $end;
    }

    public function getenderecocompletosemdcidadeAttribute($value) {
        //não usar utf8_encode pois apresenta erro na view de PDF
        $end = ($this->logradouro == '' ? '' : $this->logradouro . ' ') .
                $this->endereco .
                ($this->numero == '' ? '' : ', ' . $this->numero) .
                ($this->bairro == '' ? '' : ' - Bairro: ' . $this->bairro) .
                ($this->cep == '' ? '' : ' - CEP: ' . $this->cep) .
                ($this->complemento == '' ? '' : ' - ' . $this->complemento)
                ;
        return $end;

    }

    public function getcidadeeufAttribute($value) {
        //não usar utf8_encode pois apresenta erro na view de PDF
        $end = ($this->cidade ? $this->cidade->cidade . ' - ' . $this->cidade->estado : '');
        return $end;

    }

    public function gethorariosegquiAttribute($value)
    {

        $hr1_i = $this->segqui_hr1_i ? $this->segqui_hr1_i->format('H:i') : null;
        $hr1_f = $this->segqui_hr1_f ? $this->segqui_hr1_f->format('H:i') : null;
        $hr2_i = $this->segqui_hr2_i ? $this->segqui_hr2_i->format('H:i') : null;
        $hr2_f = $this->segqui_hr2_f ? $this->segqui_hr2_f->format('H:i') : null;

        $hr = ($hr1_i ? $hr1_i . ' às ' . $hr1_f : '');

        $hr =  ($hr == '' ? '' : $hr ) .
               ($hr2_i && $hr2_f? ' e ' . $hr2_i . ' às ' . $hr2_f : '');

        return $hr;
    }

    public function gethorariosexAttribute($value)
    {

        $hr1_i = $this->sex_hr1_i ? $this->sex_hr1_i->format('H:i') : null;
        $hr1_f = $this->sex_hr1_f ? $this->sex_hr1_f->format('H:i') : null;
        $hr2_i = $this->sex_hr2_i ? $this->sex_hr2_i->format('H:i') : null;
        $hr2_f = $this->sex_hr2_f ? $this->sex_hr2_f->format('H:i') : null;

        $hr = ($hr1_i ? $hr1_i . ' às ' . $hr1_f : '');

        $hr =  ($hr == '' ? '' : $hr ) .
               ($hr2_i && $hr2_f ? ' e ' . $hr2_i . ' às ' . $hr2_f : '');

        return $hr;
    }

    public function gethorarioportariaAttribute($value)
    {

        $hr1_i = $this->portaria_hr1_i ? $this->portaria_hr1_i->format('H:i') : null;
        $hr1_f = $this->portaria_hr1_f ? $this->portaria_hr1_f->format('H:i') : null;
        $hr2_i = $this->portaria_hr2_i ? $this->portaria_hr2_i->format('H:i') : null;
        $hr2_f = $this->portaria_hr2_f ? $this->portaria_hr2_f->format('H:i') : null;

        $hr = ($hr1_i ? $hr1_i . ' às ' . $hr1_f : '');

        $hr =  ($hr == '' ? '' : $hr ) .
               ($hr2_i && $hr2_f ? ' e ' . $hr2_i . ' às ' . $hr2_f : '');

        return $hr;
    }


    public function getenderecoAttribute($value)
    {
      return utf8_encode($value);
    }
    public function setenderecoAttribute($value)
    {
      $this->attributes['endereco'] =  utf8_decode($value);
    }

    public function getnumeroAttribute($value)
    {
      return utf8_encode($value);
    }
    public function setnumeroAttribute($value)
    {
      $this->attributes['numero'] =  utf8_decode($value);
    }

    public function getbairroAttribute($value)
    {
      return utf8_encode($value);
    }
    public function setbairroAttribute($value)
    {
      $this->attributes['bairro'] =  utf8_decode($value);
    }

    public function getcepAttribute($value)
    {
      return utf8_encode($value);
    }
    public function setcepAttribute($value)
    {
      $this->attributes['cep'] =  utf8_decode($value);
    }

    public function getcomplementoAttribute($value)
    {
      return utf8_encode($value);
    }
    public function setcomplementoAttribute($value)
    {
      $this->attributes['complemento'] =  utf8_decode($value);
    }

    public function getfiltroAttribute($value)
    {
      return utf8_encode($value);
    }
    public function setfiltroAttribute($value)
    {
      $this->attributes['filtro'] =  utf8_decode($value);
    }


}
