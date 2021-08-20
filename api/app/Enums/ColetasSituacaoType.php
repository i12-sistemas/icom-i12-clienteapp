<?php

namespace App\Enums;

use BenSampo\Enum\Enum;


/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class ColetasSituacaoType extends Enum
{
    //0 = Bloqueado, 1 = Liberado, 2 = Encerrado, 3 = Cancelado
    const tcsBloqueado = "0";
    const tcsLiberado = "1";
    const tcsEncerrado = "2";
    const tcsCancelado = "3";



    public static function getDescription($value): string
    {
        switch ($value) {
            case self::tcsBloqueado:
                return 'Bloqueada';
                break;
            case self::tcsLiberado:
                return 'Liberado';
                break;
            case self::tcsEncerrado:
                return 'Encerrada';
                break;
            case self::tcsCancelado:
                return 'Cancelada';
                break;
            default:
                return 'Desconhecido';
                break;
        }
    }
}
