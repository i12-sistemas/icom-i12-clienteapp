<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\Request;

class ColetaNotaAvulsaAddCommand extends Command
{

    protected $signature = 'coletanota:avulsaadd {maxtime}';
    protected $description = 'Insere nota avulsa com base na indexação do XML';

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
            $ret = app()->call([$cc, 'processaColetaAvulsa'], []);
            $ret = (object)$ret->getOriginalContent();
            $this->info(\json_encode($ret));
            if ($ret->ok) {
                $this->info('Processado com sucesso :: restante a processar = ' . $ret->data['restante']);
                $continua = ($ret->data['restante'] > 0);
            } else {
                $continua = false;
                $this->info($ret->msg);
            }
            $i = $i + 1;

            $now = time();
            $timeElapsed = $now - $startTime;
            $this->info('timeElapsed');
            $this->info($timeElapsed);
            if (($timeElapsed > $maxtime) && ($maxtime > 0)) $continua = false;
            sleep(0.1);
        }
        $this->info('-----------------------');
        $this->info('Processamento finalizado!');
    }
}
