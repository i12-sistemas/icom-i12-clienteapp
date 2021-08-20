<?php

namespace App\Enums;

use BenSampo\Enum\Enum;
use Exception;

final class CargaEntradaStatusEnumType extends Enum
{

   const tceEmAberto = '1';
   const tceEncerrado = '2';

   public static function getDescription($value): string
   {
       switch ($value) {
            case self::tceEmAberto:
               return 'Em Aberto';
               break;
            case self::tceEncerrado:
               return 'Encerrado';
               break;
            default:
               return 'Desconhecido';
               break;
       }
   }
}
