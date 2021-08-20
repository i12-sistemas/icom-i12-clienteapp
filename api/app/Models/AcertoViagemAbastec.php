<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcertoViagemAbastec extends Model
{
    protected $table = 'acertoviagemabastec';
    protected $dates = ['data'];
    public $timestamps = false;

    public function export($complete = true)
    {
        $dados = $this->toArray();
        $dados['data'] = $this->data ? $this->data->format('Y-m-d') : null;
        unset($dados['acertoid']);
        return $dados;
    }

    public function setkminiAttribute($value)
    {
        $this->attributes['kmini'] = $value;
        $kmfim = $this->kmfim ? $this->kmfim : 0;
        $kmtotal = $kmfim - $this->kmini;
        $this->attributes['kmtotal'] = $kmtotal;
        $this->calcmedia();
    }

    public function setkmfimAttribute($value)
    {
        $this->attributes['kmfim'] = $value;
        $kmini = $this->kmini ? $this->kmini : 0;
        $kmtotal = $this->kmfim - $kmini;
        $this->attributes['kmtotal'] = $kmtotal;
        $this->calcmedia();
    }

    public function setlitrosAttribute($value)
    {
        $this->attributes['litros'] = $value;

        $vlrabastecimento = $this->vlrabastecimento ? $this->vlrabastecimento : 0;

        $vlrlitro = 0;
        if ($this->litros !== 0) $vlrlitro = $vlrabastecimento / $this->litros;
        $this->attributes['vlrlitro'] = round($vlrlitro, 6);

        $this->calcmedia();
    }

    public function setvlrabastecimentoAttribute($value)
    {
        $this->attributes['vlrabastecimento'] = $value;

        $litros = $this->litros ? $this->litros : 0;

        $vlrlitro = 0;
        if ($litros !== 0) $vlrlitro = $this->vlrabastecimento / $litros;
        $this->attributes['vlrlitro'] = round($vlrlitro, 6);
    }

    public function calcmedia()
    {

        $kmtotal = $this->kmtotal ? $this->kmtotal : 0;
        $litros = $this->litros ? $this->litros : 0;
        if (($kmtotal === 0) || ($litros === 0)) {
            $this->attributes['media'] = 0;
        } else {
            $this->attributes['media'] = round(($kmtotal / $litros), 8);
        }
    }

}
