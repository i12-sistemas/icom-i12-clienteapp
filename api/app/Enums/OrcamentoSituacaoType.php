<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class OrcamentoSituacaoType extends Enum
{
 //0 = EmAberto, 1 = AprovadoColetaBloqueada, 2 = AprovadoColetaLiberada, 3 = Reprovado
 const tosEmAberto = "0";
 const tosAprovadoColetaBloqueada = "1";
 const tosAprovadoColetaLiberada = "2";
 const tosReprovado = "3";

 public static function getDescription($value): string
 {
     switch ($value) {
         case self::tosEmAberto:
             return 'Em aberto';
             break;
         case self::tosAprovadoColetaBloqueada:
             return 'Aprovado (Coleta bloqueada)';
             break;
         case self::tosAprovadoColetaLiberada:
             return 'Aprovado e liberado coleta';
             break;
         case self::tosReprovado:
             return 'Reprovado';
             break;
         default:
             return 'Desconhecido';
             break;
     }
 }
}
