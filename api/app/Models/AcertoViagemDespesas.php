<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcertoViagemDespesas extends Model
{
    protected $table = 'acertoviagemdespesas';
    public $timestamps = false;

    public function export($complete = true)
    {
        $dados = $this->toArray();
        $dados['despesaviagem'] = $this->despesaviagem ? $this->despesaviagem->export() : null;
        unset($dados['despesaviagemid']);
        unset($dados['acertoid']);
        return $dados;
    }

    public function despesaviagem()
    {
        return $this->hasOne(DespesaViagem::class, 'id', 'despesaviagemid');
    }

}
