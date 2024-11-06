<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Func;
use DB;
use Log;

class SendMessage implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    //public $user;
    public $message;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($no)
    {
        $rslt = DB::table("messages")->select("*")->where('no',$no)->where('send_status',"Y")->where('recv_status',"Y")->where('no',$no)->first();
        $val = (Array) $rslt;

        Log::debug($val);
        // 데이터 점검
        if( !$val['recv_id'] || !$val['send_id'] || !$val['title'] )
        {
            return false;
        }
        $this->message = $val;
    }
    
    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        // 전달 채널
        $recv_id = $this->message['recv_id'];
        return new PrivateChannel('message.'.$recv_id);
    }

    public function broadcastWith()
    {
        $val = $this->message;

        $rslt = DB::table("users")->select("*")->where('id',$val['send_id'])->where('save_status','Y')->first();
                
        $val['send_nm']   = isset($rslt->name)? Func::chungDecOne($rslt->name) : $val['send_id'];
        $val['send_time'] = Func::dateFormat($val['send_time']);
        
        return $val;
    }
}
