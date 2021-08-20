<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class BaixaIndexacaoEntradaAvulsaCommand extends Command
{
    protected $signature = 'coletanota:processocompleto';
    protected $description = 'Executa todo processo de download, indexação e entrada de coleta (em forma de cascata de outro commands)';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {

        $maxtime = 180; // 100 segundos para cada processo
        $this->info('---------------------------------------------------------------');
        $this->info(' 1 de 3 :: Fazendo o download de XML das notas de coletas no sistema BSoft');
        $this->info(' command bsoft:processanfe');
        $this->info('---------------------------------------------------------------');
        $maxtime = 60; // 100 segundos para cada processo
        $ret = $this->call('bsoft:processanfe', ['maxtime' => $maxtime]);
        $this->info('');
        $this->info('');
        $this->info('---------------------------------------------------------------');
        $this->info(' 2 de 3 :: Fazendo a indexação de dados do XML para a tabela ColetaNota');
        $this->info(' command coletanota:indexanfe');
        $this->info('---------------------------------------------------------------');
        $maxtime = 60; // 100 segundos para cada processo
        $ret = $this->call('coletanota:indexanfe', ['maxtime' => $maxtime]);
        $this->info('');
        $this->info('');
        $this->info('---------------------------------------------------------------');
        $this->info(' 3 de 3 :: Inserindo nota avulsa com base na indexação do XML');
        $this->info(' command coletanota:avulsaadd');
        $this->info('---------------------------------------------------------------');
        $ret = $this->call('coletanota:avulsaadd', ['maxtime' => $maxtime]);
        $this->info('Processamento finalizado!');
    }
}
