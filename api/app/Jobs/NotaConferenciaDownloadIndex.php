<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Models\NotaConferencia;
use Illuminate\Http\Request;

class NotaConferenciaDownloadIndex implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $nota;

    public function __construct(NotaConferencia $nota)
    {
        $this->nota = $nota;
    }

    public function handle()
    {
        $i = 1;
        $limitetentativas = 1; // numero de tentativa se houver erro em um nota
        $delaydownload = 0; // delay entre requisições da bsoft para evitar "em processamento"

        $request = new Request();
        $request->query->add(['pagesize' => 1]);
        $request->query->add(['limitetentativas' => $limitetentativas]);
        $request->query->add(['delaydownload' => $delaydownload]);
        $request->query->add(['chaves' => $this->nota->notachave]);

        $cc = app()->make('App\Http\Controllers\api\v1\BSoftNFeController');
        $ret = app()->call([$cc, 'processa_almoxarifado'], []);
        $ret = (object)$ret->getOriginalContent();
        if ($ret->ok) {
            $request = new Request();
            $request->query->add(['pagesize' => 1]);
            $request->query->add(['chaves' => $this->nota->notachave]);

            $cc = app()->make('App\Http\Controllers\api\v1\NotaConferenciaController');
            $ret = app()->call([$cc, 'processa'], []);
            $ret = (object)$ret->getOriginalContent();
            if (!$ret->ok) {
                \Log::info('Erro ao indexar: ' + $ret->msg);
            }
        } else {
            \Log::error('Erro ao processar :: ' . $ret->msg);
        }
    }
}
