<?php

namespace App\Enums;

use BenSampo\Enum\Enum;
use Exception;

final class GuaritaCheckStatusEnumType extends Enum
{

   const EmAberto = '1';
   const Encerrado = '2';

   public static function getDescription($value): string
   {
       switch ($value) {
            case self::EmAberto:
               return 'Em Aberto';
               break;
            case self::Encerrado:
               return 'Emcerrado';
               break;
            default:
               return 'Desconhecido';
               break;
       }
   }
}
