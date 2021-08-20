<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

use App\Models\Dispositivo;
use App\Models\DispositivoLink;

class SendMailLinkRequestAllow extends Mailable
{
    use Queueable, SerializesModels;

    protected $dispositivo;
    protected $link;

    public function __construct($dispositivo, $link)
    {
        $this->dispositivo = $dispositivo;
        $this->link = $link;
    }

    public function build()
    {
        $url = env('APP_URL_FRONT', '') . '/cadastro/dispositivomovel/' . $this->dispositivo->uuid . '/edit';
        return $this->to($this->link->email)
                    ->subject('Novo dispositivo cadastrado')
                    ->markdown('emails.dispositivo.linkrequestallow')
                    ->with([
                      'dispositivo'   =>  $this->dispositivo,
                      'link'          =>  $this->link,
                      'url'      =>  $url
                    ]);
    }
}
