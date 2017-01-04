<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

use App\Models\Message;
use App\Models\User;
use Exception;
use Log;

class MessageCreated implements ShouldBroadcastNow
{
    use SerializesModels;

    public $message;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        if($this->message) {
            if($this->message->receiver > 0) {
                if($this->message->sender > 0 && $this->message->sender != $this->message->receiver) {
                    return [new PrivateChannel('user.'.$this->message->receiver), new PrivateChannel('user.'.$this->message->sender)];
                }
                else {
                    return new PrivateChannel('user.'.$this->message->receiver);
                }
            }
            elseif($this->message->room_id > 0) {
                return new PresenceChannel('room.'.$this->message->room_id);
            }
        }
    }

    public function broadcastWith()
    {
        if($this->message) {
            try {
                $ids = [$this->message->sender, $this->message->receiver];
                $users = User::find($ids);
                $users = $users->getDictionary();
                $data = $this->message->toArray();
                if(isset($users[$this->message->sender])) {
                    $data['sender_name'] = $users[$this->message->sender]->name;
                }
                if(isset($users[$this->message->receiver])) {
                    $data['receiver_name'] = $users[$this->message->receiver]->name;
                }
                //Log::info('data: '.var_export($data, true));
                return $data;
            }catch(Exception $e) {
                Log::error($e->getMessage());
            }
        }
        return [];
    }

}