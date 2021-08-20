<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\Request;

class DownloadNFeBSoftCommand extends Command
{
    protected $signature = 'bsoft:processanfe {maxtime}';
    protected $description = 'Faz o download de XML das notas de coletas no sistema BSoft';

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
        $limitetentativas = 2; // numero de tentativa se houver erro em um nota
        $delaydownload = 0; // delay entre requisições da bsoft para evitar "em processamento"
        $continua = true;
        $erroconsecutivo = 0;
        $request->query->add(['pagesize' => 10]);
        $request->query->add(['limitetentativas' => $limitetentativas]);
        $request->query->add(['delaydownload' => $delaydownload]);

        $startTime = time();
        $this->info('Start time');
        $this->info($startTime);

        while ($continua) {
            $this->info($i . ' :: Processando...');
            $cc = app()->make('App\Http\Controllers\api\v1\BSoftNFeController');
            $ret = app()->call([$cc, 'processa'], []);
            $ret = (object)$ret->getOriginalContent();
            if ($ret->ok) {
                $this->info('Processado com sucesso :: restante a processar = ' . $ret->data);
                $erroconsecutivo = 0;
                $continua = ($ret->data > 0);
            } else {
                $this->error('Erro ao processar :: ' . $ret->msg);
                $erroconsecutivo = $erroconsecutivo + 1;
            }

            $now = time();
            $timeElapsed = $now - $startTime;
            $this->info('timeElapsed');
            $this->info($timeElapsed);
            if (($timeElapsed > $maxtime) && ($maxtime > 0)) $continua = false;

            if ($erroconsecutivo > 0) $continua = false;
            $i = $i + 1;

            sleep(0.1);
        }
        $this->info('-----------------------');
        $this->info('Processamento finalizado!');
    }
}
