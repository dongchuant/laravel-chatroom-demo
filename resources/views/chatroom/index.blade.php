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
    <script type="text/javascript">
    @if($user)
    window.curUser = {!! $user->toJson() !!};
    @else
    window.curUser = null;
    @endif
    window.roomId = 1;
    window.chatroom = {
        'request': function(url, data, callback) {
            if(!callback) {
                callback = function(data) {alert(data.msg);};
            }
            data._token = window.Laravel.csrfToken;
            $.post(url, data, callback, 'json');
        },

        'post': function(room, receiver, message) {
            if(!window.curUser) {
                alert('please log in');
                return;
            }
            this.request('/chatroom/post', {room:room, receiver:receiver, message:message});
        },
    };
    window.Echo = new _Echo({
        broadcaster: 'socket.io',
        host: 'http://chatroom.my:6001'
    });
    function listenMessage() {
        if(!window.curUser) {
            return;
        }
        window.Echo.private('user.'+window.curUser.id)
        .listen('MessageCreated', (e) => {
            console.log('private message');
            console.log(e);
        });
        window.Echo.join('room.'+window.roomId)
        .here((users) => {
            console.log(users);
        })
        .joining((user) => {
            console.log(user.name);
        })
        .leaving((user) => {
            console.log(user.name);
        })
        .listen('MessageCreated', (e) => {
            console.log('chatroom message');
            console.log(e);
        });
    }
    listenMessage();
    </script>
@endsection


