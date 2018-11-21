$(document).ready(function() {

    $('#user_chat_messages').niceScroll({cursorcolor:'#80C242'});

    //Добавление существующих сообщений в окно чата
    var jqxhr = $.ajax({
        type: 'POST',
        dataType: 'json',
        url: 'index.php?route=proc_jsondata_get',
        data: {chat_id: PAGE_DATA.chat_id, query: "user_chat_data"},
        beforeSend: function() {
            write_to_screen('<div class=\"ajax-loader\"></div>');
        },
        success: function(data){
            $('#user_chat_messages').empty();
            write_to_screen(data.html);
        },
        error: function(xhr, status, error){
            console.log(error);
        }
    });

    jqxhr.done(function () {
        websocket = new WebSocket(PAGE_DATA.ws_server_url);
        websocket.onopen = function(event) { ws_onopen(event) };
        websocket.onclose = function(event) { ws_onclose(event) };
        websocket.onmessage = function(event) { ws_onmessage(event) };
        websocket.onerror = function(event) { ws_onerror(event) };

        invite_users_popup_open(PAGE_DATA.chat_id);

        //Добавление нового комментария
        $('#chat_form').validate({
            submitHandler: function(form) {
                var message = $('input[name="message"]').val();
                if (message == '') {
                    //Пользователь не ввел сообщение
                    $('input[name="message"]').addClass('warning_field');
                    setTimeout(function () {
                        $('input[name="message"]').removeClass('warning_field');
                    }, 1000);
                } else {
                    var ws_data = {
                        type: 'message',
                        message: message
                    };
                    websocket.send(JSON.stringify(ws_data));
                }
            }
        });

    });

});

function ws_onopen(event) {
    websocket.send(PAGE_DATA.ws_open_params);
    console.log('open');
}

function ws_onclose(event) {
    console.log('disconnected');
    // location.reload();
}

function ws_onmessage(event) {
    var event_data = JSON.parse(event.data),
        type = event_data.type,
        user_id = event_data.user_id,
        display_name = event_data.display_name,
        image_url = event_data.image_url;

    var msg = '';
    switch(type) {
        case 'system_message':
            console.log(user_id + ': connected');
            break;

        case 'message':
            $('input[name="message"]').val('');
            msg = event_data.message;
            var timestamp = event_data.timestamp,
                message_created_time = event_data.message_created_time;
            if (msg != '') {

                var line = '<div class="line"><div class="comment_autor_image"><div class="profile_image" style="background: url(' + image_url + ') 100% 100% no-repeat;  background-size: cover;"></div></div><div class="comment_message"><span>' + display_name + '</span> ' + msg + '</div><div class=\"msg_created_date\">' + message_created_time + '</div></div>';
                write_to_screen(line);
            }
            break;

        case 'invite':
            invited_user_display_name = event_data.invited_user_display_name;
            write_to_screen('<div class="chat_date_line text-center">' + display_name + ' ' + PAGE_DATA.INVITED_TO_CHAT + ' ' + invited_user_display_name + '</div>');
            break;
    }

    console.log(event.data);
}

function ws_onerror(event) {
    websocket.close();
    console.log(event.data);
    // location.reload();
}

function write_to_screen(message) {
    $container = $('#user_chat_messages');
    $container.append(message);
    $container[0].scrollTop = $container[0].scrollHeight;
}

function invite_users_popup_open(chat_id){

    $('.invite_users').click(function(){
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: 'index.php?route=proc_jsondata_get',
            data: {query: 'invite_users_list', chat_id: chat_id},
            success: function (data) {
                console.log(data);
                var invite_user_profiles_content = '';
                if (data.status == 'OK' && data.data.length > 0) {

                    $.each(data.data, function(key, value){
                        var user_id = parseInt(value.id),
                            display_name = value.display_name,
                            profile_image_url = value.profile_image;

                        invite_user_profiles_content += '<div class="invite_user_profile"><div class="profile_info"><div class="profile_image" style="background: url(' + profile_image_url + ') 100% 100% no-repeat;  background-size: cover;"></div>' + display_name + '</div><div><input class="invite_users_checkbox" type="checkbox" data-user-id="' + user_id + '"></div></div>';
                    });

                } else {
                    invite_user_profiles_content = 'No users';
                }

                $('#popup-invite-users .popup_content').html(invite_user_profiles_content);
                $.magnificPopup.open({
                    items: {
                        src: '#popup-invite-users',
                        type: 'inline',
                        preloader: false,
                        modal: true
                    }
                });
                
                $('.popup-invite-users-close-icon').click(function () {
                    $('#popup-invite-users').magnificPopup('close');
                });

                invite_users_confirm();
            },
            error: function (xhr, status, error) {
                console.log(error);
            }
        });
    });
}

function invite_users_confirm() {
    $('#invite_users_confirm').click(function () {
        var invite_users_id_list = [];
        $('.invite_users_checkbox').each(function () {
            var user_id = $(this).attr('data-user-id');
            if ($(this).prop('checked')) {
                invite_users_id_list.push(user_id);
                var ws_data = {
                    type: 'invite',
                    sid: PAGE_DATA.sid,
                    invited_user_id: user_id
                };
                websocket.send(JSON.stringify(ws_data));
            }

        });
        $('#popup-invite-users').magnificPopup('close');
        console.log(invite_users_id_list);
    });
}