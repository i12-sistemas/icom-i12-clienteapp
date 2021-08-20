<?php

namespace App\Enums;

use BenSampo\Enum\Enum;
use Exception;

final class PaleteStatusEnumType extends Enum
{

   const EmAberto = '1';
   const Lacrado = '2';
   const Despachado = '3';
   const Cancelado = '4';

   public static function getDescription($value): string
   {
       switch ($value) {
            case self::EmAberto:
               return 'Em Aberto';
               break;
            case self::Lacrado:
               return 'Lacrado';
               break;
            case self::Despachado:
               return 'Despachado';
               break;
            case self::Cancelado:
                return 'Cancelado';
                break;
            default:
               return 'Desconhecido';
               break;
       }
   }
}
