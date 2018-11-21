$(document).ready(function() {
    $('#chat_messages').niceScroll({cursorcolor: '#80C242'});

    $('#btn_open_dialog').click(function () {
        $('#support_chat_wrapper').show();
    });

    websocket = new WebSocket(SUPPORT_PAGE_DATA.websocket_server_url);
    websocket.onopen = function(event) { ws_onopen(event) };
    websocket.onclose = function(event) { ws_onclose(event) };
    websocket.onmessage = function(event) { ws_onmessage(event) };
    websocket.onerror = function(event) { ws_onerror(event) };

});


function ws_onopen(event) {
    var ws_data = {
        type: 'system_message',
        client_status: 'admin',
        client_id: SUPPORT_PAGE_DATA.admin_id,
        lang: SUPPORT_PAGE_DATA.lang
    };

    websocket.send(JSON.stringify(ws_data));
}

function ws_onclose(event) {
    $('.ws_state').html('ws connection closed');
    console.log('disconnected');
}

function ws_onmessage(event) {
    var event_data = JSON.parse(event.data);
    var type = event_data.type;

    message = '';
    switch(type) {
        case 'system_message':
            message = Constants.CONNECTED;
            break;

        case 'waiting_chats':
            var waiting_chats = event_data.waiting_chats;
            $('.pending_chats_container tbody').empty();
            for (chat_id in waiting_chats){
                user_id = waiting_chats[chat_id][0];
                user_display_name = waiting_chats[chat_id][1];
                user_image_url = waiting_chats[chat_id][2];

                created_timestamp = waiting_chats[chat_id][3];
                var date = new Date(created_timestamp*1000);
                var year = date.getFullYear();
                var month = '0' + date.getUTCMonth() + 1;
                var day = date.getUTCDate();
                var hours = date.getHours();
                var minutes = '0' + date.getMinutes();
                var seconds = '0' + date.getSeconds();
                created_timestamp = day + '.' + month.substr(-2) + '.' + year + ' ' + hours + ':' + minutes.substr(-2) + ':' + seconds.substr(-2);

                messages = waiting_chats[chat_id][4];
                messages_data = '';

                for (timestamp in messages){
                    message = messages[timestamp];
                    var date = new Date(timestamp*1000);
                    var hours = date.getHours();
                    var minutes = "0" + date.getMinutes();
                    var seconds = "0" + date.getSeconds();
                    timestamp = hours + ':' + minutes.substr(-2) + ':' + seconds.substr(-2);
                    messages_data = messages_data + '<div>' + timestamp + '  ' + message + '</div>';
                }

                waiting_chat_data = '<tr><td><div class="profile_info"><div class="profile_image" style="background: url(' + user_image_url + ') 100% 100% no-repeat;  background-size: cover;"></div><div class="profile_name"><span>' + user_display_name + '</span></div></td><td>' + created_timestamp + '</td><td> ' + messages_data + ' </td><td><button class="btn btn-danger accept_chat_btn" data-chat-id="' + chat_id + '" style="margin-left: 5px">Принять чат</button></td></tr>';
                $('.pending_chats_container tbody').append(waiting_chat_data);
            }
            if (jQuery.isEmptyObject(waiting_chats)) {
                $('.pending_chats_container tbody').html('<tr><td colspan="4" class="text-center">No waiting chats</tr>');
            }
            accept_chat();
            break;

        case 'chat_accepted':
            var chat_id = event_data.chat_id;
            var user_id = event_data.user_id;
            var user_display_name = event_data.user_display_name;
            var user_image_url = event_data.user_image_url;

            var existing_messages = event_data.existing_messages;
            messages_data = '';
            for (timestamp in existing_messages){
                message = existing_messages[timestamp];
                var date = new Date(timestamp*1000);
                var hours = date.getHours();
                var minutes = "0" + date.getMinutes();
                var seconds = "0" + date.getSeconds();
                timestamp = hours + ':' + minutes.substr(-2) + ':' + seconds.substr(-2);
                line = '<div class="line"><div class="comment_autor_image"><div class="profile_image" style="background: url(' + user_image_url + ') 100% 100% no-repeat;  background-size: cover;"></div></div><div class="comment_message"><span>' + user_display_name + '</span>' + message + '</div></div>';

                messages_data = messages_data + line;
            }

            profile_info = '<div class="profile_info"><div class="profile_image" style="background: url(' + user_image_url + ') 100% 100% no-repeat;  background-size: cover;"></div><div class="profile_name"><span>' + user_display_name + '</span>';

            active_chat = '<div class="col-xs-6" data-chat-id="' + chat_id + '"><div class="admin_panel"><div class="admin_panel_title"><h2> ' + profile_info +'</h2><ul class="nav navbar-right panel_toolbox"><li><a class="close-link close_chat_icon" data-chat-id="' + chat_id + '"><i class="fa fa-close"></i></a></li></ul><div class="clearfix"></div><small class="connection_state"></small></div><div class="admin_panel_content support_chat_wrapper"><div id="support_chat_messages_'+ chat_id + '" class="support_chat_messages">' + messages_data + '</div><form id="chat_'+ chat_id + '" name="chat_'+ chat_id + '" method="post"><div class="support_chat_bottom"><div class="left"><input type="text" name="message" maxlength="255" placeholder="Напишите свое сообщение"></div><div class="right"><input type="submit" class="btn theme-button" name="send-comment" value="Отправить"></div></div></form></div><div class="clear"></div></div></div>';

            $('.active_chats_container').append(active_chat);
            close_chat();
            send_message(chat_id);
            break;

        case 'user_disconnected':
            var chat_id = event_data.chat_id;
            $('[data-chat-id="' + chat_id + '"]').find('.connection_state').html('<p style="color: #c7254e">disconnected</p>');
            break;

        case 'message':
            var chat_id = event_data.chat_id;
            var client_display_name = event_data.client_display_name;
            var client_image_url = event_data.client_image_url;
            var message = event_data.message;
            message = '<div class="line"><div class="comment_autor_image"><div class="profile_image" style="background: url(' + client_image_url + ') 100% 100% no-repeat;  background-size: cover;"></div></div><div class="comment_message"><span>' + client_display_name + '</span>' + message + '</div></div>';
            write_to_screen(message, chat_id);
            break;

    }

    console.log(event.data);
}

function ws_onerror(event) {
    websocket.close();
    console.log(event);
}

function write_to_screen(message, chat_id) {
    $container = $('#support_chat_messages_'+ chat_id);
    $container.append(message);
    $container[0].scrollTop = $container[0].scrollHeight;
}

function accept_chat(){
    $('.accept_chat_btn').click(function () {
        var chat_id = $(this).attr('data-chat-id');
        var ws_data = {
            type: 'accept_chat',
            client_status: 'admin',
            client_id: SUPPORT_PAGE_DATA.admin_id,
            chat_id: chat_id,
            lang: SUPPORT_PAGE_DATA.lang
        };

        websocket.send(JSON.stringify(ws_data));
    });
}

function close_chat(){
    $('.close_chat_icon').click(function (e) {
        e.preventDefault();
        var chat_id = $(this).attr('data-chat-id');
        var ws_data = {
            type: 'close_chat',
            client_status: 'admin',
            client_id: SUPPORT_PAGE_DATA.admin_id,
            chat_id: chat_id,
            lang: SUPPORT_PAGE_DATA.lang
        };

        websocket.send(JSON.stringify(ws_data));
    });
}

function send_message(chat_id){
    //Добавление нового сообщения
    $('#chat_' + chat_id).validate({
        submitHandler: function(form) {
            var $message_input = $('#chat_' + chat_id + ' input[name="message"]');
            var message = $message_input.val();
            if (message == '') {
                $message_input.addClass('warning_field');
                setTimeout(function () {
                    $message_input.removeClass('warning_field');
                }, 1000);
            } else {
                var ws_data = {
                    type: 'message',
                    client_status: 'admin',
                    client_id: SUPPORT_PAGE_DATA.admin_id,
                    chat_id: chat_id,
                    message: message,
                    lang: SUPPORT_PAGE_DATA.lang
                };
                websocket.send(JSON.stringify(ws_data));
                $message_input.val('');
            }
        }
    });
}