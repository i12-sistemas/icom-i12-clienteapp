<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

final class ColetasStatusVirtualType extends Enum
{
    const Aberto_RevOrcamento = "1";
    const Aberto_NaoLiberado = "2";
    const Aberto_Atrasado = "3";
    const Aberto_Ontem = "4";
    const Aberto_Hoje = "5";
    const Aberto_Futuro = "6";
    const Encerrado_Interno = "7";
    const Encerrado_Motorista = "8";
}



