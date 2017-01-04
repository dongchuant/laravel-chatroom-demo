@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading header-login">
                    @if($user)
                    You are logged in! {{$user->name}}
                    @else
                    Please login in...
                    @endif
                </div>

                <div class="panel-body">
                    <form class="form-inline" role="form" action="" style="display: inline-block;" onsubmit="return false;">
                        <label class="control-label">Send to: </label>
                        <select name="receiver" id="receiver" class="form-control">
                            <option value="0">Everyone</option>
                        </select>
                        <input name="content" id="content" class="form-control" type="input" placeholder="input message..." style="width: 400px;">
                        <input type="button" class="btn btn-success btn-send" value="Send"/>
                    </form>
                </div>

                <div class="panel-body" style="overflow:scroll; height:450px;">
                    <ul class="list-group message-list">
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

{{-- Scripts --}}
@section('scripts')
    @parent
    <script src="/js/socket.io-1.4.5.js"></script>
    <script src="/js/echo.js"></script>
    <script src="/js/chatroom.js"></script>
    <script type="text/javascript">
    @if($user)
    window.curUser = {!! $user->toJson() !!};
    @else
    window.curUser = null;
    @endif
    window.roomId = {{$roomId}};
    @if(!empty($messages))
    window.messages = {!! $messages->toJson() !!};
    for(var i in messages) {
        chatroom.showMessage(messages[i]);
    }
    @endif
    function listenMessage() {
        if(!window.curUser) {
            window.location.href = '/login';
            return;
        }
        window.Echo = new _Echo({
            broadcaster: 'socket.io',
            host: 'http://chatroom.my:6001'
        });
        window.Echo.private('user.'+window.curUser.id)
        .listen('MessageCreated', (e) => {
            console.log('private message');
            console.log(e);
            chatroom.showMessage(e);
        });
        window.Echo.join('room.'+window.roomId)
        .here((users) => {
            console.log(users);
            if(users) {
                for(var i in users) {
                    chatroom.addUser(users[i]);
                }
            }
        })
        .joining((user) => {
            var msg = user.name + ' joining';
            console.log(msg);
            chatroom.showMessage({sender: 0, receiver: 0, content:msg});
            chatroom.addUser(user);
        })
        .leaving((user) => {
            var msg = user.name + ' leaving';
            console.log(msg);
            chatroom.showMessage({sender: 0, receiver: 0, content:msg});
            chatroom.delUser(user);
        })
        .listen('MessageCreated', (e) => {
            console.log('chatroom message');
            console.log(e);
            chatroom.showMessage(e);
        });
    }
    $(function(){
        $(".btn-send").click(function(){
            var receiver = $('#receiver').val();
            var message = $.trim($("#content").val());
            if(!message) {
                alert('please input message');
                return;
            }
            chatroom.post(roomId, receiver, message);
        });
        listenMessage();
    });
    </script>
@endsection


