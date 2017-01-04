<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Message;
use App\Events\MessageCreated;

class ChatroomController extends Controller
{

    public function index(Request $request)
    {
        $user = $request->user();
        if($user) {
            $messages = Message::where('room_id', 1)->where(function($query) use($user){
                $query->where('receiver', 0)->orWhere('receiver', $user->id)->orWhere('sender', $user->id);
            })->take(50)->get();
            $uids = $messages->pluck('sender')->toArray();
            $uids = array_merge($uids, $messages->pluck('receiver')->toArray());
            $uids = array_unique($uids);
            if($uids) {
                $users = User::find($uids);
                $users = $users->getDictionary();
                foreach ($messages as $message) {
                    if(isset($users[$message->sender])) {
                        $message->sender_name = $users[$message->sender]->name;
                    }
                    if(isset($users[$message->receiver])) {
                        $message->receiver_name = $users[$message->receiver]->name;
                    }
                }
            }
        }
        return view('chatroom/index', ['user' => $user, 'roomId' => 1, 'messages' => isset($messages) ? $messages : null]);
    }

    public function post(Request $request)
    {
        $user = $request->user();
        if(!$user) {
            return $this->error('please log in');
        }
        $room = (int)$request->input('room');
        $receiver = (int)$request->input('receiver');
        $message = $request->input('message');
        $room = max(0, $room);
        $receiver = max(0, $receiver);
        $message = Message::create(['room_id' => $room, 'sender' => $user->id, 'receiver' => $receiver, 'content' => $message]);
        if(!$message) {
            return $this->error('failed');
        }
        event(new MessageCreated($message));
        return $this->success(null, 'send successfully');
    }
}
