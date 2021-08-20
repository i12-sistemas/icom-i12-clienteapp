<?php

namespace App\Enums;

use BenSampo\Enum\Enum;
use Exception;

final class CargaEntregaStatusEnumType extends Enum
{

   const tceEmAberto = '1';
   const tceLiberadoCarregarEntrega = '2';
   const tceEmTransito = '3';
   const tceEntregue = '4';


   public static function getDescription($value): string
   {
       switch ($value) {
            case self::tceEmAberto:
               return 'Em Aberto';
               break;
            case self::tceLiberadoCarregarEntrega:
               return 'Liberado para carregamento e/ou entrega';
               break;
            case self::tceEmTransito:
               return 'Em trânsito';
               break;
            case self::tceEntregue:
                return 'Entregue';
                break;
            default:
               return 'Desconhecido';
               break;
       }
   }
}
