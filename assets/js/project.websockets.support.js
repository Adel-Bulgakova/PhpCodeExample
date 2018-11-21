$(document).ready(function() {
    $('#chat_messages').niceScroll({cursorcolor: '#80C242'});

    $('#btn_open_dialog').click(function () {
        $('#support_chat_wrapper').show();
        $(this).remove();

        websocket = new WebSocket(SUPPORT_PAGE_DATA.websocket_server_url);
        websocket.onopen = function(event) { ws_onopen(event) };
        websocket.onclose = function(event) { ws_onclose(event) };
        websocket.onmessage = function(event) { ws_onmessage(event) };
        websocket.onerror = function(event) { ws_onerror(event) };
    });

});

function ws_onopen(event) {
    var ws_data = {
        type: 'system_message',
        sid: SUPPORT_PAGE_DATA.sid,
        client_status: 'user',
        client_id: SUPPORT_PAGE_DATA.user_id,
        lang: SUPPORT_PAGE_DATA.lang
    };

    websocket.send(JSON.stringify(ws_data));
}

function ws_onclose(event) {
    system_message = '<div class="line"><div class="comment_autor_image"><div class="profile_image" style="background: url(/assets/images/default_profile.jpg) 100% 100% no-repeat;  background-size: cover;"></div></div><div class="comment_message"><span>System</span>' + Constants.CONNECTION_CLOSED + '</div></div>';
    write_to_screen(system_message);
    console.log(event);
    console.log('disconnected');
}

function ws_onmessage(event) {
    var event_data = JSON.parse(event.data);
    var type = event_data.type;

    message = '';
    switch(type) {
        case 'system_message':
            var admins = event_data.online_admins;
            var chat_id = event_data.chat_id;
            if (admins > 0) {
                text = 'Пожалуйста подождите. Вам ответит первый освободившийся оператор.';
            } else {
                text = 'К сожалению все операторы заняты. Оставьте сообщение  и мы свяжемся с Вами в ближайшее время.';
            }
            system_message = '<div class="line"><div class="comment_autor_image"><div class="profile_image" style="background: url(/assets/images/default_profile.jpg) 100% 100% no-repeat;  background-size: cover;"></div></div><div class="comment_message"><span>System</span>' + text + '</div></div>';            
            message_add(chat_id);
            write_to_screen(system_message);
            break;

        case 'chat_accepted':
            var admin_display_name = event_data.admin_display_name;
            var admin_image_url = event_data.admin_image_url;
            message = '<div class="line"><div class="comment_autor_image"><div class="profile_image" style="background: url(' + admin_image_url + ') 100% 100% no-repeat;  background-size: cover;"></div></div><div class="comment_message"><span>' + admin_display_name + '</span>Здравствуйте!</div></div>';
            write_to_screen(message);
            break;

        case 'admin_disconnected':
            var admin_display_name = event_data.admin_display_name;
            var admin_image_url = event_data.admin_image_url;
            message = '<div class="line"><div class="comment_autor_image"><div class="profile_image" style="background: url(' + admin_image_url + ') 100% 100% no-repeat;  background-size: cover;"></div></div><div class="comment_message"><span>' + admin_display_name + '</span>disconnected</div></div>';
            write_to_screen(message);
            break;

        case 'message':
            var client_display_name = event_data.client_display_name;
            var client_image_url = event_data.client_image_url;
            var message = event_data.message;
            message = '<div class="line"><div class="comment_autor_image"><div class="profile_image" style="background: url(' + client_image_url + ') 100% 100% no-repeat;  background-size: cover;"></div></div><div class="comment_message"><span>' + client_display_name + '</span>' + message + '</div></div>';
            write_to_screen(message);
            break;
    }

    console.log(event.data);
}

function ws_onerror(event) {
    websocket.close();
    console.log(event);
}

function write_to_screen(message) {
    $container = $('#support_chat_messages');
    $container.append(message);
    $container[0].scrollTop = $container[0].scrollHeight;
}

function message_add(chat_id) {
    //Добавление нового сообщения
    $('#support_chat_form').validate({
        submitHandler: function(form) {

            var message = $('input[name="message"]').val();
            if (message == '') {
                $('input[name="message"]').addClass('warning_field');
                setTimeout(function () {
                    $('input[name="message"]').removeClass('warning_field');
                }, 1000);
            } else {
                var ws_data = {
                    type: 'message',
                    client_status: 'user',
                    client_id: SUPPORT_PAGE_DATA.user_id,
                    chat_id: chat_id,
                    message: message,
                    lang: SUPPORT_PAGE_DATA.lang
                };
                websocket.send(JSON.stringify(ws_data));
                $('input[name="message"]').val('');
            }
        }
    });
}