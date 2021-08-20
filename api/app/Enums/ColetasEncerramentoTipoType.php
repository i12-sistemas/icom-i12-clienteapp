<?php

namespace App\Enums;

use BenSampo\Enum\Enum;
use Exception;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class ColetasEncerramentoTipoType extends Enum
{

   const tetInterno = '1';
   const tetAplicativoMotorista = '2';
   const tetPainelCliente = '3';
   const tetReaberturaOrcamento = '4';



   public static function getDescription($value): string
   {
       switch ($value) {
            case self::tetInterno:
               return 'Interno';
               break;
            case self::tetAplicativoMotorista:
               return 'App do Motorista';
               break;
            case self::tetPainelCliente:
               return 'Painel do cliente';
               break;
            case self::tetReaberturaOrcamento:
                return 'Reabertura do orçamento';
                break;
            default:
               return 'Desconhecido';
               break;
       }
   }
}
