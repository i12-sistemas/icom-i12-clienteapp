<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Models\FollowupFiles;
use Illuminate\Http\Request;
use App\Events\NotificationEvent;

class FollowUpStartProcessaPlanilhaJob implements ShouldQueue
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
        $ret = app()->call([$cc, 'readplanilha'], [ 'id' => $this->filefup->id, 'forceleiturafup' => false ]);
        $ret = (object)$ret->getOriginalContent();
        if ($ret->ok) {
            \Log::info('Processado com sucesso');
        } else {
            \Log::error('Erro ao processar :: ' . $ret->msg);
        }

        $channel = 'usr-' . mb_strtolower($this->filefup->created_usuario->login);
        $msg = 'O arquivo ' . $this->filefup->nomeoriginal . ' referente ao dia ' . $this->filefup->dataref->format('Y-m-d') .
               ' foi processado ' . ($ret->ok ? ' com sucesso!' : ' com erros. Erro: ' . $ret->msg);
        $url = env('APP_URL_FRONT') . '/followup/planilhas/import/consulta?id=' . $this->filefup->id;
        $notificacao = [
            'title' => 'Follow Up - Arquivo processado' . ($ret->ok ? ' com sucesso!' : ' com erros!'),
            'msg' => $msg,
            'icon' => ($ret->ok ? 'thumb_up' : 'error'),
            'color' => ($ret->ok ? 'positive' : 'negative'),
            'url' => $url,
            'urltarget' => '_self',
            'urllabel' => 'Abrir listagem de arquivos',
        ];

        event(new NotificationEvent($channel, 'info', $notificacao));
    }
}
