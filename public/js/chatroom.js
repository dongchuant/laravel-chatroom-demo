window.chatroom = {
    'request': function(url, data, callback) {
        if(!callback) {
            callback = function(data) {alert(data.msg);};
        }
        data._token = window.Laravel.csrfToken;
        $.post(url, data, callback, 'json');
    },

    'sending': false,
    'post': function(room, receiver, message) {
        if(!window.curUser) {
            alert('please log in');
            return;
        }
        if(this.sending) {
            alert('sending...');
            return;
        }
        this.sending = true;
        this.request('/chatroom/post', {room:room, receiver:receiver, message:message}, function(data){
            chatroom.sending = false;
            if(data && data.code==0) {
                $("#content").val('');
            }
            else {
                alert(data.msg);
            }
        });
    },

    'addUser': function(user) {
        if($("#user-"+user.id).length > 0) {
            return;
        }
        $("#receiver").append('<option id="user-'+user.id+'" value="'+user.id+'">'+user.name+'</option>');
    },

    'delUser': function(user) {
        $("#user-"+user.id).remove();
    },

    'showMessage': function(message) {
        var messageList = $('.message-list');
        if(messageList.length < 1) {
            alert(msgType + ': '+ message.content);
            return;
        }
        var item = '<li class="list-group-item">';
        if(message.created_at) {
            item += '['+message.created_at+']';
        }
        var curUid = window.curUser.id;
        var tip = '';
        if(message.receiver > 0) {
            if(message.sender > 0) {
                tip = '[Private]'+(curUid == message.sender ? '[To: '+message.receiver_name+']' : '');
            }
            else {
                tip = '[System Private]';
            }
        }
        else if(message.sender <= 0) {
            tip = '[System]';
        }
        if(tip) {
           tip = '<span style="color: red">'+tip+'</span>';
        }
        item += tip;
        if(message.sender_name) {
            item += message.sender_name + ': ';
        }
        else if(message.sender > 0) {
            item += 'Anonymous: ';
        }
        item += message.content;
        item += '</li>';
        if(messageList.find('li').length >= 50) {
            messageList.find('li:last').remove();
        }
        messageList.prepend(item);
    }
};