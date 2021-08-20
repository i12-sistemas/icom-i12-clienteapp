<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Models\FollowupFiles;
use Illuminate\Http\Request;


class FollowUpExportPlanilhaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    protected $filefup;
    public $timeout = 10800;

    public function __construct(FollowupFiles $filefup)
    {
        $this->filefup = $filefup;
    }


    public function handle()
    {
        $i = 1;
        $limitetentativas = 1; // numero de tentativa se houver erro em um nota
        $delaydownload = 0; // delay entre requisições da bsoft para evitar "em processamento"

        $cc = app()->make('App\Http\Controllers\api\v1\FollowupFilesController');
        $ret = app()->call([$cc, 'exportprocessa'], [ 'fileid' => $this->filefup->id  ]);
        $ret = (object)$ret->getOriginalContent();
        if ($ret->ok) {
            \Log::info('Processado com sucesso');
        } else {
            \Log::error('Erro ao processar :: ' . $ret->msg);
        }
    }
}
