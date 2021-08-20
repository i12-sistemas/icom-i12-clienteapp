<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClienteUsuario extends Model
{
    protected $table = 'clienteusuario';
    protected $hidden = ['senha', 'fotostorage', 'fotofilename', 'fotoext', 'fotosize'];
    protected $dates = ['ultimoacesso', 'created_at', 'updated_at'];

    public function toObject($showCompact = false)
    {
        $dados = $this->toArray();
        $dados['ultimoacesso'] = $this->ultimoacesso ? $this->ultimoacesso->format('Y-m-d H:i:s') : null;
        $dados['created_at'] = $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null;
        $dados['updated_at'] = $this->updated_at ? $this->updated_at->format('Y-m-d H:i:s') : null;


        $dados['created_usuario'] = $this->created_usuario ? $this->created_usuario->toSmall() : null;
        $dados['updated_usuario'] = $this->updated_usuario ? $this->updated_usuario->toSmall() : null;
        unset($dados['created_usuarioid']);
        unset($dados['updated_usuarioid']);
        return $dados;
    }


    public function toObjectPainel($auth)
    {
        $dados = $this->toArray();
        $dados['ultimoacesso'] = $this->ultimoacesso ? $this->ultimoacesso->format('Y-m-d H:i:s') : null;
        $dados['created_at'] = $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null;
        $dados['updated_at'] = $this->updated_at ? $this->updated_at->format('Y-m-d H:i:s') : null;
        unset($dados['id']);
        unset($dados['clienteid']);
        unset($dados['created_at']);
        unset($dados['updated_at']);
        unset($dados['created_usuarioid']);
        unset($dados['updated_usuarioid']);
        if ($auth){
            $dados['username'] = $auth['username'];
            $dados['usernametype'] = $auth['usernametype'];
        }
        $dados['cliente'] = [
            'fantasia' => $this->cliente->fantasia,
            'razaosocial' => $this->cliente->razaosocial,
            'fantasia_followup' => $this->cliente->fantasia_followup,
            'followupid' => $this->cliente->followupid,
        ];
        return $dados;
    }

    public function getnomeAttribute($value)
    {
      return utf8_encode($value);
    }
    public function setnomeAttribute($value)
    {
      $this->attributes['nome'] =  utf8_decode($value);
    }

    public function getcelularAttribute($value)
    {
      return utf8_encode($value);
    }
    public function setcelularAttribute($value)
    {
        $celular = cleanDocMask(trim(utf8_decode($value)));
        if ($celular === '') $celular = null;
      $this->attributes['celular'] =  ($celular ? $celular : null);
    }

    public function getfotofilenameAttribute($value)
    {
      return utf8_encode($value);
    }
    public function setfotofilenameAttribute($value)
    {
      $this->attributes['fotofilename'] =  utf8_decode($value);
    }

    public function cliente()
    {
        return $this->hasOne(Cliente::class, 'id', 'clienteid');
    }

    public function created_usuario()
    {
        return $this->hasOne(Usuario::class, 'id', 'created_usuarioid');
    }

    public function updated_usuario()
    {
        return $this->hasOne(Usuario::class, 'id', 'updated_usuarioid');
    }


    public function toCompleteArray()
    {
        $cliente = null;
        if ($this->cliente) {
            $cidade  = null;
            if ($this->cliente->cidade) {
                $cidade = [
                  'cidade' => $this->cliente->cidade->cidade,
                  'uf' => $this->cliente->cidade->uf,
                  'regiao' => $this->cliente->cidade->regiao ? $this->cliente->cidade->regiao->regiao : ''
                ];
            }
            $cliente = [
                'fantasia' => $this->cliente->fantasia,
                'razaosocial' => $this->cliente->razaosocial,
                'fantasia_followup' => $this->cliente->fantasia_followup,
                'followupid' => $this->cliente->followupid,
                'cidade' => $cidade,
                'cnpj' => $this->cliente->cnpj,
            ];
        }

        $dados = [
            'id' => $this->id,
            'email' => $this->email,
            'celular' => $this->celular,
            'nome' => $this->nome,
            'ativo' => $this->ativo,
            'fotourl' => $this->fotourl,
            'cliente' => $cliente
        ];
        return $dados;
    }

    // public function permissoesLiberadas () {
    //     $qry = \DB::select(\DB::raw("select permissao.id
    //             from permissao
    //             inner join perfilacesso_permissoes on perfilacesso_permissoes.permissaoid=permissao.id
    //             inner join perfilacesso on perfilacesso.id=perfilacesso_permissoes.perfilid and perfilacesso.ativo=1
    //             inner join usuario_perfil on usuario_perfil.perfilid=perfilacesso.id
    //             where permissao.grupo=0 and usuario_perfil.usuarioid=:idusuario
    //             group by permissao.id"), ['idusuario' => $this->id] );

    //     $permissoes = [];
    //     foreach ($qry as $value) {
    //         $permissoes[] = $value->id;
    //     }

    //     return $permissoes;

    // }

}
