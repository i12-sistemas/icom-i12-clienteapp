<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PerfilAcesso extends Model
{
    protected $table = 'perfilacesso';

    protected $dates = ['created_at', 'updated_at'];

    public function toObject()
    {
        $dados = $this->toArray();

        $p = [];
        foreach ($this->permissoes as $permissao) {
            $p[] = $permissao->permissaoid;
        }
        if (count($p)>0) $dados['permissoes'] = $p;
        $dados['permissaocount'] =count($p);

        $dados['usuariocount'] = count($this->perfis);


        $dados['created_usuario'] = $this->created_usuario->toObject(false);
        $dados['updated_usuario'] = $this->updated_usuario->toObject(false);
        unset($dados['created_usuarioid']);
        unset($dados['updated_usuarioid']);
        return $dados;
    }

    public function permissoes()
    {
        return $this->hasMany(PerfilacessoPermissoes::class, 'perfilid', 'id');
    }

    public function created_usuario()
    {
        return $this->hasOne(Usuario::class, 'id', 'created_usuarioid');
    }

    public function updated_usuario()
    {
        return $this->hasOne(Usuario::class, 'id', 'updated_usuarioid');
    }

    public function perfis()
    {
        return $this->hasMany(UsuarioPerfil::class, 'perfilid', 'id')->with('usuario', 'created_usuario');
        // return $this->hasManyThrough(
        //     Usuario::class,
        //     UsuarioPerfil::class,
        //     'perfilid',
        //     'id',
        //     'id',
        //     'usuarioid'

        // );
        // return $this->hasManyThrough(
        //     Usuario::class,
        //     UsuarioPerfil::class,
        //     'id',
        //     'id',
        //     'usuarioid',
        //     'perfilid'
        // );
    }

    public function getdescricaoAttribute($value)
    {
      return utf8_encode($value);
    }
    public function setdescricaoAttribute($value)
    {
      $this->attributes['descricao'] =  utf8_decode($value);
    }

}
