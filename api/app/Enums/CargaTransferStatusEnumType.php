<?php

namespace App\Enums;

use BenSampo\Enum\Enum;
use Exception;

final class CargaTransferStatusEnumType extends Enum
{

   const tctEmAberto = '1';
   const tctLiberadoCarregarTrans = '2';
   const tctEmTransito = '3';
   const tctEncerrado = '4';

   public static function getDescription($value): string
   {
       switch ($value) {
            case self::tctEmAberto:
               return 'Em Aberto';
               break;
            case self::tctLiberadoCarregarTrans:
               return 'Liberado para carregamento e/ou transfêrencia';
               break;
            case self::tctEmTransito:
               return 'Em trânsito';
               break;
            case self::tctEncerrado:
                return 'Encerrado';
                break;
            default:
               return 'Desconhecido';
               break;
       }
   }
}
