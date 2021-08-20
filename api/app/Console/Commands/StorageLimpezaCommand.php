<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class StorageLimpezaCommand extends Command
{
    protected $signature = 'storage:limpezaarquivos';
    protected $description = 'Faz a limpeza de arquivos temporarios do disco';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Processando limpeza de arquivos...');
        $cc = app()->make('App\Http\Controllers\api\v1\StorageManutencaoController');
        $ret = app()->call([$cc, 'limpeza'], []);
        $ret = (object)$ret->getOriginalContent();
        if ($ret->ok) {
            $this->info('Processado com sucesso!');
            $this->info($ret->msg);
        } else {
            $this->error('Erro ao processar!');
            $this->error($ret->msg);
        }
        $this->info('-----------------------');
        $this->info('Processamento finalizado!');
    }
}
