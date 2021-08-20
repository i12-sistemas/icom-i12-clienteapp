<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Mail\SendMailLinkRequestAllow;
use Mail;

class SendMailLinkRequestAllowJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $dispositivo;
    protected $link;

    public function __construct($dispositivo, $link)
    {
        $this->dispositivo = $dispositivo;
        $this->link = $link;
    }


    public function handle()
    {
        $email = new SendMailLinkRequestAllow($this->dispositivo, $this->link);
        Mail::send($email);
    }
}
