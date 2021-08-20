<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

use Carbon\Carbon;

class NotificationEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private $channel;
    private $bind;
    public $id;
    public $title;
    public $msg;
    public $icon;
    public $created_at;
    public $url;
    public $urltarget;
    public $urllabel;

    public function __construct($channel, $bind, $notification)
    {
        $this->channel = $channel;
        $this->bind = $bind;
        $this->created_at = Carbon::now()->format('Y-m-d H:i:s');
        $this->id = md5(Str::uuid() . $this->created_at);
        if (isset($notification['title'])) $this->title = $notification['title'];
        if (isset($notification['msg'])) $this->msg = $notification['msg'];
        if (isset($notification['icon'])) $this->icon = $notification['icon'];
        if (isset($notification['url'])) $this->url = $notification['url'];
        if (isset($notification['urltarget'])) $this->urltarget = $notification['urltarget'];
        if (isset($notification['urllabel'])) $this->urllabel = $notification['urllabel'];
    }

    // public function broadcastWith () {
    //     return [
    //         'id'       => 122,
    //         'name'     => 'ddddddddddd'
    //     ];
    // }

    public function broadcastAs()
    {
        return $this->bind;
    }

    public function broadcastOn()
    {
        return [$this->channel];
    }
}
