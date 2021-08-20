<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class AcertoViagemPeriodo extends Model
{
    protected $table = 'acertoviagemperiodo';
    protected $dates = ['dhi', 'dhf'];
    public $timestamps = false;

    public function export($complete = true)
    {
        $dados = $this->toArray();
        $dados['dhi'] = $this->dhi ? $this->dhi->format('Y-m-d H:i:s') : null;
        $dados['dhf'] = $this->dhf ? $this->dhf->format('Y-m-d H:i:s') : null;
        unset($dados['acertoid']);
        return $dados;
    }

    public function getobsAttribute($value)
    {
      return utf8_encode($value);
    }
    public function setobsAttribute($value)
    {
      $this->attributes['obs'] =  utf8_decode($value);
    }

    public function setdhiAttribute($value)
    {
      $this->attributes['dhi'] = $value;
      $totalmin = 0;
      if (($this->dhf) && ($this->dhi)) {
        $totalmin = $this->dhf->diffInMinutes($this->dhi);
      }
      $this->attributes['totalmin'] = $totalmin;
    }

    public function setdhfAttribute($value)
    {
      $this->attributes['dhf'] = $value;
      $totalmin = 0;
      if (($this->dhf) && ($this->dhi)) {
        $totalmin = $this->dhf->diffInMinutes($this->dhi);
      }
      $this->attributes['totalmin'] = $totalmin;
    }


    public function calculardiarias($pParams)
    {
        $day = 0;

        $di = Carbon::createFromFormat('Y-m-d', $this->dhi->format('Y-m-d'));
        $df = Carbon::createFromFormat('Y-m-d', $this->dhf->format('Y-m-d'));
        if (($di) && ($df)) {
            $day = $df->diffInDays($di) + 1;
        }
        if ($day < 0) $day = 0;

        $this->attributes['qtdedias'] = $day;

        $cafestart = $pParams['cafe'];
        $almocostart = $pParams['almoco'];
        $jantarstart = $pParams['jantar'];
        $pernoitestart = $pParams['pernoite'];

        $hri = Carbon::createFromFormat('H:i', $this->dhi->format('H:i'));
        $hrf = Carbon::createFromFormat('H:i', $this->dhf->format('H:i'));

        // Calcular os almoços a serem pagos
        $almocoqtde = intVal($this->qtdedias);
        if ($hri > $almocostart) $almocoqtde = $almocoqtde - 1;
        //   if Hora_Ini > qParALMOCO_COR_F.Value then Almoco := Almoco - 1; //Se o motorista saiu   no dia após  as  11:31 então não recebe o almoço inicial.
        if ($hrf <= $almocostart) $almocoqtde = $almocoqtde - 1;
        //   if Hora_Fim <= qParALMOCO_COR_F.Value then Almoco := Almoco - 1; //Se o motorista chegou no dia antes das 11:30 então não recebe o almoço final.
        $this->attributes['almocoqtde'] = $almocoqtde;

        // Calcular os jantar a serem pagos
        $jantarqtde = intVal($this->qtdedias);
        if ($hri > $jantarstart) $jantarqtde = $jantarqtde - 1;
        if ($hrf <= $jantarstart) $jantarqtde = $jantarqtde - 1;
        $this->attributes['jantarqtde'] = $jantarqtde;

        // Calcular os pernoite a serem pagos
        $pernoiteqtde = intVal($this->qtdedias);
        if ($hri > $pernoitestart) $pernoiteqtde = $pernoiteqtde - 1;
        if ($hrf <= $pernoitestart) $pernoiteqtde = $pernoiteqtde - 1;
        $this->attributes['pernoiteqtde'] = $pernoiteqtde;

        // Calcular os cafés a serem pagos
        $cafeqtde = intVal($this->qtdedias);
        if ($hri > $cafestart) $cafeqtde = $cafeqtde - 1;
        if ($hrf <= $cafestart) $cafeqtde = $cafeqtde - 1;
        // Cafe := Cafe - Pernoite;
        $cafeqtde = $cafeqtde - $pernoiteqtde;
        // if Cafe < 0 then Cafe := 0; //O café é embutido na pernoite, então desconto o total de pernoites dos cafés.
        if ($cafeqtde < 0) $cafeqtde = 0;
        $this->attributes['cafeqtde'] = $cafeqtde;
    }

}
