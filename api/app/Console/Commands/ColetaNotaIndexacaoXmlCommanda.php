<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\Request;

class ColetaNotaIndexacaoXmlCommanda extends Command
{

    protected $signature = 'coletanota:indexanfe {maxtime}';
    protected $description = 'Faz a indexação de dados do XML para a tabela ColetaNota';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle(Request $request)
    {
        $maxtime = $this->argument('maxtime');
        $maxtime = $maxtime ? intval($maxtime) : 0;
        $this->info('maxtime=' . $maxtime);

        $i = 1;
        $continua = true;
        $request->query->add(['pagesize' => 10]);

        $startTime = time();
        $this->info('Start time');
        $this->info($startTime);

        while ($continua) {
            $this->info($i . ' :: Processando...');
            $cc = app()->make('App\Http\Controllers\api\v1\ColetasNotasController');
            $ret = app()->call([$cc, 'processa'], []);
            $ret = (object)$ret->getOriginalContent();
            if ($ret->ok) {
                $this->info('Processado com sucesso :: restante a processar = ' . $ret->data);
                $continua = ($ret->data > 0);
            } else {
                $continua = false;
                $this->info($ret->msg);
            }

            $now = time();
            $timeElapsed = $now - $startTime;
            $this->info('timeElapsed');
            $this->info($timeElapsed);
            if (($timeElapsed > $maxtime) && ($maxtime > 0)) $continua = false;

            $i = $i + 1;
            sleep(0.1);
        }
        $this->info('-----------------------');
        $this->info('Processamento finalizado!');
    }
}
