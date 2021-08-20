<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Usuario extends Model
{
    protected $table = 'usuario';
    protected $hidden = ['senha'];

    public function getnomeAttribute($value)
    {
      return utf8_encode($value);
    }
    public function setnomeAttribute($value)
    {
      $this->attributes['nome'] =  utf8_decode($value);
    }

    public function getloginAttribute($value)
    {
      return utf8_encode($value);
    }
    public function setloginAttribute($value)
    {
      $this->attributes['login'] =  utf8_decode($value);
    }
    public function getdefaulturlAttribute($value)
    {
      return utf8_encode($value);
    }
    public function setdefaulturlAttribute($value)
    {
      $this->attributes['defaulturl'] =  utf8_decode($value);
    }

    public function getfotourlAttribute($value)
    {
      return utf8_encode($value);
    }
    public function setfotourlAttribute($value)
    {
      $this->attributes['fotourl'] =  utf8_decode($value);
    }

    public function unidadeprincipal()
    {
        return $this->hasOne(Unidade::class, 'id', 'unidadeprincipalid');
    }


    public function created_usuario()
    {
        return $this->hasOne(Usuario::class, 'id', 'created_usuarioid');
    }

    public function updated_usuario()
    {
        return $this->hasOne(Usuario::class, 'id', 'updated_usuarioid');
    }

    public function unidades()
    {
        return $this->hasMany(UsuarioUnidade::class, 'usuarioid', 'id');
    }


    public function toSmall()
    {
        $dados = [
            'id' => $this->id,
            'login' => $this->login,
            'nome' => $this->nome,
            'fotourl' => $this->fotourl
        ];
        return $dados;
    }


    public function toCompleteArray()
    {
        $unidades = [];
        foreach ($this->unidades as $unidade) {
            $unidades[] = [
                'id' => $unidade->id,
                'unidade' => $unidade->unidade->toSimple(),
                'created_at' => $unidade->created_at ? $unidade->created_at->format('Y-m-d H:i:s') : null,
                'created_usuario' => $unidade->created_usuario ? $unidade->created_usuario->toSimple() : null
            ];
        }

        $dados = [
            'id' => $this->id,
            'login' => $this->login,
            'nome' => $this->nome,
            'ativo' => $this->ativo,
            'email' => $this->email,
            'fotourl' => $this->fotourl,
            'unidadeprincipal' => $this->unidadeprincipal ? $this->unidadeprincipal->toSimple() : null,
            'unidades' => count($unidades) > 0 ? $unidades : null,
            'defaulturl' => $this->defaulturl
        ];
        return $dados;
    }

    public function permissoesLiberadas () {
        $qry = \DB::select(\DB::raw("select permissao.id
                from permissao
                inner join perfilacesso_permissoes on perfilacesso_permissoes.permissaoid=permissao.id
                inner join perfilacesso on perfilacesso.id=perfilacesso_permissoes.perfilid and perfilacesso.ativo=1
                inner join usuario_perfil on usuario_perfil.perfilid=perfilacesso.id
                where permissao.grupo=0 and usuario_perfil.usuarioid=:idusuario
                group by permissao.id"), ['idusuario' => $this->id] );

        $permissoes = [];
        foreach ($qry as $value) {
            $permissoes[] = $value->id;
        }

        return $permissoes;

    }

    public function toObject($showcomplete = false)
    {
        if ($showcomplete) {
            $dados = $this->toArray();
            unset($dados['senha']);
            $dados['unidadeprincipal'] = $this->unidadeprincipal ? $this->unidadeprincipal->toSimple() : null;
            unset($dados['unidadeprincipalid']);

            $unidades = [];
            foreach ($this->unidades as $unidade) {
                $unidades[] = [
                    'id' => $unidade->id,
                    'unidade' => $unidade->unidade->toSimple(),
                    'created_at' => $unidade->created_at ? $unidade->created_at->format('Y-m-d H:i:s') : null,
                    'created_usuario' => $unidade->created_usuario ? $unidade->created_usuario->toSimple() : null
                ];
            }
            $dados['unidades'] = count($unidades) > 0 ? $unidades : null;

            $dados['created_usuario'] = $this->created_usuario->toObject(false);
            unset($dados['created_usuarioid']);
            $dados['updated_usuario'] = $this->updated_usuario->toObject(false);
            unset($dados['updated_usuarioid']);
        } else {
            $unidades = [];
            foreach ($this->unidades as $unidade) {
                $unidades[] = [
                    'id' => $unidade->id,
                    'unidade' => $unidade->unidade->toSimple(),
                    'created_at' => $unidade->created_at ? $unidade->created_at->format('Y-m-d H:i:s') : null,
                    'created_usuario' => $unidade->created_usuario ? $unidade->created_usuario->toSimple() : null
                ];
            }

            $dados = [
                'id' => $this->id,
                'nome' => $this->nome,
                'ativo' => $this->ativo,
                'fotourl' => $this->fotourl,
                'unidadeprincipal' => $this->unidadeprincipal ? $this->unidadeprincipal->toSimple() : null,
                'unidades' => count($unidades) > 0 ? $unidades : null
            ];
        }

        return $dados;
    }

    public function toSimple()
    {
        $dados = [
            'id' => $this->id,
            'nome' => $this->nome,
            'ativo' => $this->ativo,
            'fotourl' => $this->fotourl,
        ];
        return $dados;
    }
}
