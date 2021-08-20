<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaletesItem extends Model
{
    protected $table = 'paletesitem';
    public $timestamps = false;

    public function export($complete = true)
    {
        $dados = [
            'id' => $this->id
        ];
        unset($dados['paleteid']);
        $dados['etiqueta'] = $this->etiqueta ? $this->etiqueta->export($complete) : null;
        return $dados;
    }



    public function palete()
    {
        return $this->hasOne(Paletes::class, 'id', 'paleteid');
    }

    public function etiqueta()
    {
        return $this->hasOne(Etiquetas::class, 'ean13', 'ean13');
    }

}
