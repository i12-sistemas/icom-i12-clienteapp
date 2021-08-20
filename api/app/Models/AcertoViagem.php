<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class AcertoViagem extends Model
{
    protected $table = 'acertoviagem';
    protected $dates = ['dhacerto'];

    public function export($complete = true)
    {
        $dados = $this->toArray();
        $dados['dhacerto'] = $this->dhacerto ? $this->dhacerto->format('Y-m-d H:i:s') : null;
        $dados['created_at'] = $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null;
        $dados['updated_at'] = $this->updated_at ? $this->updated_at->format('Y-m-d H:i:s') : null;

        $dados['cafehrbc'] = $this->cafehrbc ? $this->cafehrbc->format('H:i') : null;
        $dados['almocohrbc'] = $this->almocohrbc ? $this->almocohrbc->format('H:i') : null;
        $dados['jantarhrbc'] = $this->jantarhrbc ? $this->jantarhrbc->format('H:i') : null;
        $dados['pernoitehrbc'] = $this->pernoitehrbc ? $this->pernoitehrbc->format('H:i') : null;

        $dados['cidadeorigem'] = $this->cidadeorigem ? $this->cidadeorigem->toObject(!$complete) : null;
        $dados['cidadedestino'] = $this->cidadedestino ? $this->cidadedestino->toObject(!$complete) : null;
        $dados['motorista'] = $this->motorista ? $this->motorista->toObject(!$complete) : null;
        $dados['veiculo'] = $this->veiculo ? $this->veiculo->toObject(!$complete) : null;
        $dados['veiculocarreta'] = $this->veiculocarreta ? $this->veiculocarreta->toObject(!$complete) : null;

        unset($dados['cidadeorigemid']);
        unset($dados['cidadedestinoid']);
        unset($dados['motoristaid']);
        unset($dados['veiculoid']);
        unset($dados['veiculocarretaid']);


        $aPeriodos = [];
        foreach ($this->periodos as $e) {
            $aE = $e->export();
            $aPeriodos[] = $aE;
        }
        unset($dados['periodos']);
        $dados['periodos'] = $aPeriodos;


        $aAbastecimentos = [];
        foreach ($this->abastecimentos as $abastecimento) {
            $aAbastecimentos[] = $abastecimento->export();
        }
        unset($dados['abastecimentos']);
        $dados['abastecimentos'] = $aAbastecimentos;

        $aDespesas = [];
        foreach ($this->despesas as $despesa) {
            $aDespesas[] = $despesa->export();
        }
        unset($dados['despesas']);
        $dados['despesas'] = $aDespesas;

        $aRoteiro = [];
        foreach ($this->roteiro as $rota) {
            $aRoteiro[] = $rota->export();
        }
        unset($dados['roteiro']);
        $dados['roteiro'] = $aRoteiro;


        $dados['created_usuario'] = $this->created_usuario ? $this->created_usuario->toObject(false) : null;
        $dados['updated_usuario'] = $this->updated_usuario ? $this->updated_usuario->toObject(false) : null;
        unset($dados['created_usuarioid']);
        unset($dados['updated_usuarioid']);

        // if ($showCompact) {
        //     unset($dados['cnpjmemo']);
        // }
        return $dados;
    }

    public function periodos()
    {
        return $this->hasMany(AcertoViagemPeriodo::class, 'acertoid', 'id')->orderBy('ordem', 'asc')->orderBy('id', 'asc');
    }

    public function abastecimentos()
    {
        return $this->hasMany(AcertoViagemAbastec::class, 'acertoid', 'id')->orderBy('data', 'asc')->orderBy('kmini', 'asc')->orderBy('id', 'asc');
    }

    public function despesas()
    {
        return $this->hasMany(AcertoViagemDespesas::class, 'acertoid', 'id')->orderBy('id', 'asc');
    }

    public function roteiro()
    {
        return $this->hasMany(AcertoViagemRoteiro::class, 'acertoid', 'id')->with('cidade')->orderBy('ordem', 'asc')->orderBy('rota', 'asc');
    }

    public function created_usuario()
    {
        return $this->hasOne(Usuario::class, 'id', 'created_usuarioid');
    }

    public function updated_usuario()
    {
        return $this->hasOne(Usuario::class, 'id', 'updated_usuarioid');
    }

    public function cidadeorigem()
    {
        return $this->hasOne(Cidades::class, 'id', 'cidadeorigemid');
    }

    public function cidadedestino()
    {
        return $this->hasOne(Cidades::class, 'id', 'cidadedestinoid');
    }

    public function motorista()
    {
        return $this->hasOne(Motorista::class, 'id', 'motoristaid');
    }

    public function veiculo()
    {
        return $this->hasOne(Veiculo::class, 'id', 'veiculoid');
    }

    public function veiculocarreta()
    {
        return $this->hasOne(Veiculo::class, 'id', 'veiculocarretaid');
    }

    public function getipAttribute($value)
    {
      return utf8_encode($value);
    }
    public function setipAttribute($value)
    {
      $this->attributes['ip'] =  utf8_decode($value);
    }

    public function setcafeqtdeAttribute($value)
    {
        $cafeqtde = intVal($value);
        if ($cafeqtde < 0) $cafeqtde = 0;

        $cafeextra = intVal($this->cafeextra);
        if ($cafeextra < 0) $cafeextra = 0;

        $cafevlr = $this->cafevlr;
        if ($cafevlr < 0) $cafevlr = 0;

        $cafetotal = $cafeqtde + $cafeextra;
        $cafeapagar = round($cafetotal * $cafevlr, 2);

        $this->attributes['cafeqtde'] = $cafeqtde;
        $this->attributes['cafetotal'] = $cafetotal;
        $this->attributes['cafeapagar'] = $cafeapagar;
    }

    public function setcafeextraAttribute($value)
    {
        $cafeextra = intVal($value);
        if ($cafeextra < 0) $cafeextra = 0;

        $cafeqtde = intVal($this->cafeqtde);
        if ($cafeqtde < 0) $cafeqtde = 0;

        $cafevlr = $this->cafevlr;
        if ($cafevlr < 0) $cafevlr = 0;

        $cafetotal = $cafeqtde + $cafeextra;
        $cafeapagar = round($cafetotal * $cafevlr, 2);

        $this->attributes['cafeextra'] = $cafeextra;
        $this->attributes['cafetotal'] = $cafetotal;
        $this->attributes['cafeapagar'] = $cafeapagar;
    }

    public function setalmocoqtdeAttribute($value)
    {
        $almocoqtde = intVal($value);
        if ($almocoqtde < 0) $almocoqtde = 0;

        $almocoextra = intVal($this->almocoextra);
        if ($almocoextra < 0) $almocoextra = 0;

        $almocovlr = $this->almocovlr;
        if ($almocovlr < 0) $almocovlr = 0;

        $almocototal = $almocoqtde + $almocoextra;
        $almocoapagar = round($almocototal * $almocovlr, 2);

        $this->attributes['almocoqtde'] = $almocoqtde;
        $this->attributes['almocototal'] = $almocototal;
        $this->attributes['almocoapagar'] = $almocoapagar;
    }

    public function setalmocoextraAttribute($value)
    {
        $almocoextra = intVal($value);
        if ($almocoextra < 0) $almocoextra = 0;

        $almocoqtde = intVal($this->almocoqtde);
        if ($almocoqtde < 0) $almocoqtde = 0;

        $almocovlr = $this->almocovlr;
        if ($almocovlr < 0) $almocovlr = 0;

        $almocototal = $almocoqtde + $almocoextra;
        $almocoapagar = round($almocototal * $almocovlr, 2);

        $this->attributes['almocoextra'] = $almocoextra;
        $this->attributes['almocototal'] = $almocototal;
        $this->attributes['almocoapagar'] = $almocoapagar;
    }

    public function setjantarqtdeAttribute($value)
    {
        $jantarqtde = intVal($value);
        if ($jantarqtde < 0) $jantarqtde = 0;

        $jantarextra = intVal($this->jantarextra);
        if ($jantarextra < 0) $jantarextra = 0;

        $jantarvlr = $this->jantarvlr;
        if ($jantarvlr < 0) $jantarvlr = 0;

        $jantartotal = $jantarqtde + $jantarextra;
        $jantarapagar = round($jantartotal * $jantarvlr, 2);

        $this->attributes['jantarqtde'] = $jantarqtde;
        $this->attributes['jantartotal'] = $jantartotal;
        $this->attributes['jantarapagar'] = $jantarapagar;
    }

    public function setjantarextraAttribute($value)
    {
        $jantarextra = intVal($value);
        if ($jantarextra < 0) $jantarextra = 0;

        $jantarqtde = intVal($this->jantarqtde);
        if ($jantarqtde < 0) $jantarqtde = 0;

        $jantarvlr = $this->jantarvlr;
        if ($jantarvlr < 0) $jantarvlr = 0;

        $jantartotal = $jantarqtde + $jantarextra;
        $jantarapagar = round($jantartotal * $jantarvlr, 2);

        $this->attributes['jantarextra'] = $jantarextra;
        $this->attributes['jantartotal'] = $jantartotal;
        $this->attributes['jantarapagar'] = $jantarapagar;
    }

    public function setpernoiteqtdeAttribute($value)
    {
        $pernoiteqtde = intVal($value);
        if ($pernoiteqtde < 0) $pernoiteqtde = 0;

        $pernoiteextra = intVal($this->pernoiteextra);
        if ($pernoiteextra < 0) $pernoiteextra = 0;

        $pernoitevlr = $this->pernoitevlr;
        if ($pernoitevlr < 0) $pernoitevlr = 0;

        $pernoitetotal = $pernoiteqtde + $pernoiteextra;
        $pernoiteapagar = round($pernoitetotal * $pernoitevlr, 2);

        $this->attributes['pernoiteqtde'] = $pernoiteqtde;
        $this->attributes['pernoitetotal'] = $pernoitetotal;
        $this->attributes['pernoiteapagar'] = $pernoiteapagar;
    }

    public function setpernoiteextraAttribute($value)
    {
        $pernoiteextra = intVal($value);
        if ($pernoiteextra < 0) $pernoiteextra = 0;

        $pernoiteqtde = intVal($this->pernoiteqtde);
        if ($pernoiteqtde < 0) $pernoiteqtde = 0;

        $pernoitevlr = $this->pernoitevlr;
        if ($pernoitevlr < 0) $pernoitevlr = 0;

        $pernoitetotal = $pernoiteqtde + $pernoiteextra;
        $pernoiteapagar = round($pernoitetotal * $pernoitevlr, 2);

        $this->attributes['pernoiteextra'] = $pernoiteextra;
        $this->attributes['pernoitetotal'] = $pernoitetotal;
        $this->attributes['pernoiteapagar'] = $pernoiteapagar;
    }

    public function getcafehrbcAttribute($value)
    {
        try {
            return Carbon::createFromFormat('H:i:s', $value);
        } catch (\Throwable $th) {
            return null;
        }

    }

    public function getalmocohrbcAttribute($value)
    {
        try {
            return Carbon::createFromFormat('H:i:s', $value);
        } catch (\Throwable $th) {
            return null;
        }

    }

    public function getjantarhrbcAttribute($value)
    {
        try {
            return Carbon::createFromFormat('H:i:s', $value);
        } catch (\Throwable $th) {
            return null;
        }

    }

    public function getpernoitehrbcAttribute($value)
    {
        try {
            return Carbon::createFromFormat('H:i:s', $value);
        } catch (\Throwable $th) {
            return null;
        }

    }

}
