<?php

namespace App\Jobs\ColetasNotas;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use Mail;
use App\Mail\ColetasNotas\EmailTokenXMLPendentePorUsuarioMail;
use App\Models\ColetasNotaXMLToken;

class EmailTokenXMLPendentePorUsuarioJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $token;

    public function __construct(ColetasNotaXMLToken $token)
    {
        $this->token = $token;
    }

    public function handle()
    {
        $email = new EmailTokenXMLPendentePorUsuarioMail($this->token);
        Mail::send($email);
    }
}
