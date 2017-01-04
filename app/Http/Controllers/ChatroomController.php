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
        return view('chatroom/index', ['user' => $user]);
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
