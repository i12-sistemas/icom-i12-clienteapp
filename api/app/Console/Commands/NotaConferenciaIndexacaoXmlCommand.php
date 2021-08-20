<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\Request;

class NotaConferenciaIndexacaoXmlCommand extends Command
{
    protected $signature = 'notaconferencia:indexanfe';
    protected $description = 'Faz a indexação de dados do XML para a tabela NotaConferencia';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle(Request $request)
    {
        $i = 1;
        $continua = true;
        $request->query->add(['pagesize' => 20]);

        while ($continua) {
            $this->info($i . ' :: Processando...');
            $cc = app()->make('App\Http\Controllers\api\v1\NotaConferenciaController');
            $ret = app()->call([$cc, 'processa'], []);
            $ret = (object)$ret->getOriginalContent();
            if ($ret->ok) {
                $this->info('Processado com sucesso :: restante a processar = ' . $ret->data);
                $continua = ($ret->data > 0);
            } else {
                $continua = false;
                $this->info($ret->msg);
            }
            $i = $i + 1;
            sleep(0.1);
        }
        $this->info('-----------------------');
        $this->info('Processamento finalizado!');
    }
}
