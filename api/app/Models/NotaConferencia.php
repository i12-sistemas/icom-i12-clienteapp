<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class NotaConferencia extends Model
{
    protected $table = 'nota_conferencia';
    protected $dates = ['created_at', 'updated_at', 'baixado_at'];

    public function export($complete = true)
    {
        $dados = $this->toArray();
        $dados['created_at'] = $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null;
        $dados['updated_at'] = $this->updated_at ? $this->updated_at->format('Y-m-d H:i:s') : null;
        $dados['baixado_at'] = $this->baixado_at ? $this->baixado_at->format('Y-m-d H:i:s') : null;
        $dados['cliente'] = $this->cliente ? $this->cliente->toSmallObject() : null;
        $dados['coletaid'] = $this->coletanota ? $this->coletanota->idcoleta : null;
        $dados['diastotal'] = $this->diastotal;
        unset($dados['clienteid']);
        unset($dados['created_usuarioid']);
        unset($dados['updated_usuarioid']);
        unset($dados['baixado_usuarioid']);
        $dados['created_usuario'] = $this->created_usuario ? $this->created_usuario->toObject(false) : null;
        $dados['updated_usuario'] = $this->updated_usuario ? $this->updated_usuario->toObject(false) : null;

        if ($this->baixado == 1) $dados['baixado_usuario'] = $this->baixado_usuario ? $this->baixado_usuario->toObject(false) : null;
        return $dados;
    }

    public function getdiastotalAttribute($value)
    {
        $day = 0;
        $di = Carbon::createFromFormat('Y-m-d', $this->created_at->format('Y-m-d'));
        $df = $this->baixado == 1 ? $this->baixado_at : Carbon::now();
        $df = Carbon::createFromFormat('Y-m-d', $df->format('Y-m-d'));
        if (($di) && ($df)) $day = $df->diffInDays($di);
        if ($day < 0) $day = 0;

        return $day;
    }

    public function cliente()
    {
        return $this->hasOne(Cliente::class, 'id', 'clienteid')->with('cidade');
    }

    public function coletanota()
    {
        return $this->hasOne(ColetasNota::class, 'notachave', 'notachave');
    }


    public function created_usuario()
    {
        return $this->hasOne(Usuario::class, 'id', 'created_usuarioid');
    }

    public function updated_usuario()
    {
        return $this->hasOne(Usuario::class, 'id', 'updated_usuarioid');
    }

    public function baixado_usuario()
    {
        return $this->hasOne(Usuario::class, 'id', 'baixado_usuarioid');
    }


    public function getstorageurlAttribute($value)
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
}
