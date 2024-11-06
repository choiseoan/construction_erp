<?php

namespace Illuminate\Broadcasting;

use Illuminate\Contracts\Broadcasting\HasBroadcastChannel;
use Log;
class PrivateChannel extends Channel
{
    /**
     * Create a new channel instance.
     *
     * @param  \Illuminate\Contracts\Broadcasting\HasBroadcastChannel|string  $name
     * @return void
     */
    public function __construct($name)
    {
        Log::debug($name instanceof HasBroadcastChannel);
        $name = $name instanceof HasBroadcastChannel ? $name->broadcastChannel() : $name;
        $tt = parent::__construct('private-'.$name);
    }
}
