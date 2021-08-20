<?php

namespace App\Enums;

use BenSampo\Enum\Enum;
use Exception;

final class EtiquetasStatusEnumType extends Enum
{

   const EmDeposito = '1';
   const EmTransferencia = '2';
   const EmEntrega = '3';
   const Entregue = '4';
   const Extraviado = '5';


   public static function getDescription($value): string
   {
       switch ($value) {
            case self::EmDeposito:
               return 'Depósto';
               break;
            case self::EmTransferencia:
               return 'Em transfêrencia';
               break;
            case self::EmEntrega:
               return 'Em entrega';
               break;
            case self::Entregue:
                return 'Entregue';
                break;
            case self::Extraviado:
                return 'Extraviado';
                break;
            default:
               return 'Desconhecido';
               break;
       }
   }
}
