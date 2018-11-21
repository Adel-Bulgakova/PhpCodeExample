$(document).ready(function() {

    $('#chat_messages').niceScroll({cursorcolor:'#80C242'});

    //Добавление существующих сообщений в окно чата
    var jqxhr = $.ajax({
        type: 'GET',
        dataType: 'json',
        url: 'index.php?route=proc_chat_get',
        data: {stream_id: STREAM_DATA.stream_id},
        beforeSend: function() {
            write_to_screen('<div class=\"ajax-loader\"></div>');
        },
        success: function(data){
            $('#chat_messages').empty();
            if (data.html != 'empty') {
                write_to_screen(data.html);
                // if (data.permissions == 1) {
                //     block_user(stream_id);
                // }
            }
        },
        error: function(xhr, status, error){
            console.log(error);
        }
    });

    jqxhr.done(function () {
        websocket = new WebSocket(STREAM_DATA.websocket_server_url);
        websocket.onopen = function(event) { ws_onopen(event) };
        websocket.onclose = function(event) { ws_onclose(event) };
        websocket.onmessage = function(event) { ws_onmessage(event) };
        websocket.onerror = function(event) { ws_onerror(event) };

        //Добавление нового комментария
        $('#chat_form').validate({
            submitHandler: function(form) {
                var message = $('input[name="message"]').val();
                if (STREAM_DATA.user_auth_state == 0){//Пользователь не авторизирован
                    window.location = '/';
                } else {
                    if (STREAM_DATA.user_chat_blocked_state == 1){
                        $.magnificPopup.open({
                            items: {
                                src: '#popup-blocked-info',
                                type: 'inline',
                                preloader: false,
                                modal: true
                            }
                        });
                        $('.popup-blocked-info-close-icon').click(function () {
                            $('#popup-blocked-info').magnificPopup('close');
                        });
                    } else {
                        if (message == '') {
                            //Пользователь не ввел сообщение
                            $('input[name="message"]').addClass('warning_field');
                            setTimeout(function () {
                                $('input[name="message"]').removeClass('warning_field');
                            }, 1000);
                        } else {
                            var ws_data = {
                                type: 'message',
                                stream_uuid: STREAM_DATA.stream_uuid,
                                lang: STREAM_DATA.lang,
                                client_id: STREAM_DATA.client_id,
                                text: message
                            };
                            websocket.send(JSON.stringify(ws_data));
                        }
                    }
                }
            }
        });

        $('#like').click(function(){
            var action = $('#like').attr('data-next-action');
            if (STREAM_DATA.user_auth_state == 0){
                window.location = '/';
            } else {
                var ws_data = {
                    type: 'like',
                    stream_uuid: STREAM_DATA.stream_uuid,
                    lang: STREAM_DATA.lang,
                    client_id: STREAM_DATA.client_id
                };
                websocket.send(JSON.stringify(ws_data));
            }
        });

        $('#follow').click(function(){
            if (STREAM_DATA.user_auth_state == 0){
                window.location = '/';
            } else {
                var ws_data = {
                    type: 'follow',
                    stream_uuid: STREAM_DATA.stream_uuid,
                    lang: STREAM_DATA.lang,
                    client_id: STREAM_DATA.client_id,
                    hero_id: STREAM_DATA.hero_id
                };
                websocket.send(JSON.stringify(ws_data));
            }
        });

        $('#claim').click(function(){
            if (STREAM_DATA.user_auth_state == 0){
                window.location = '/';
            } else {
                $.magnificPopup.open({
                    items: {
                        src: '#popup-claim-confirm',
                        type: 'inline',
                        preloader: false,
                        modal: true
                    }
                });
                $('.popup-claim-confirm-close-icon').click(function () {
                    $('#ppopup-claim-confirm').magnificPopup('close');
                });

                var message = Constants.REQUEST_FAILED_INFO;
                $('#claim_confirm').click(function(){
                    $.ajax({
                        type: 'POST',
                        dataType: 'json',
                        url: 'index.php?route=proc_claim',
                        data: {stream_uuid: STREAM_DATA.stream_uuid},
                        success: function(data){
                            if (data.status == "OK") {
                                var message = Constants.CLAIM_ACCEPTED;
                            }
                            $('#popup-claim-confirm .popup_content p').html(message);
                            $('#popup-claim-confirm .popup_body_control').html('');
                            $('#claim').attr('disabled');
                            console.log(data);
                        },
                        error: function(xhr, status, error){
                            $('#popup-claim-confirm .popup_content').html(message);
                            $('#claim').attr('disabled');
                            console.log(error);
                        }
                    });
                });
            }
        });
    });

    $('.link_embed').click(function(){
        var embed_options =  {
            embed_width: 640,
            embed_height: 360,
            auto_play: false,
            mute: false,
            aspect_ratio: 16 / 9,
            min_player_dimension: {
                width: 288,
                height: 162
            }
        };

        $.magnificPopup.open({
            items: {
                src: '#popup-embed',
                type: 'inline',
                preloader: false,
                modal: true
            }
        });

        if (STREAM_DATA.embed_permissions) {
            $('.js-embed_url').html('<iframe src=\"/api/v1/player?stream_uuid=' + STREAM_DATA.stream_uuid + '&width=' + embed_options.embed_width + '&height=' + embed_options.embed_height + '&auto_play=' + embed_options.auto_play + '&mute=' + embed_options.mute + '\" width=\"' + embed_options.embed_width + '\" height=\"' + embed_options.embed_height + '\" frameborder=\"0\" scrolling=\"no\"></iframe>');
            $('#embed_size').change(function(){
                var embed_size = $(this).val();
                if (embed_size === 'custom'){
                    $(".js-custom_options").show();

                    $('.js-custom_options input').each(function(){
                        $(this).change(function(){
                            var data = $(this).val();
                            if ($(this).hasClass("js-custom_width")) {
                                var embed_width = Math.max(embed_options.min_player_dimension.width, parseInt(data, 10)),
                                    embed_height = Math.round(embed_width / embed_options.aspect_ratio);

                            } else {
                                var embed_height = Math.max(embed_options.min_player_dimension.height, parseInt(data, 10)),
                                    embed_width = Math.round(embed_height * embed_options.aspect_ratio);
                            }
                            $('.js-custom_width').val(embed_width);
                            $('.js-custom_height').val(embed_height);
                            change_embed_link(embed_width, embed_height);
                        })
                    });
                } else {
                    $(".js-custom_options").hide();
                    var params = embed_size.split("x");
                    change_embed_link(params[0], params[1]);
                }
            });
            $('.input_option').each(function(){
                $(this).change(function(){
                    var embed_size = $('#embed_size').val();
                    var params = embed_size.split("x");
                    change_embed_link(params[0], params[1]);
                })
            });
        } else {
            $('#popup-embed').html('Error embed permissions');
        }

        $('.popup-embed-close-icon').click(function () {
            $('#popup-embed').magnificPopup('close');
        });
    });

    wrap_resize();
});

function ws_onopen(event) {
    var ws_data = {
        type: 'system_message',
        sid: STREAM_DATA.sid,
        stream_uuid: STREAM_DATA.stream_uuid,
        lang: STREAM_DATA.lang,
        client_id: STREAM_DATA.client_id
    };

    websocket.send(JSON.stringify(ws_data));
}

function ws_onclose(event) {
    system_message = '<div class="line"><div class="comment_autor_image"><div class="profile_image" style="background: url(/assets/images/default_profile.jpg) 100% 100% no-repeat;  background-size: cover;"></div></div><div class="comment_message"><span>System</span>' + Constants.CONNECTION_CLOSED + '</div></div>';
    write_to_screen(system_message);
    console.log('disconnected');
}

function ws_onmessage(event) {
    var event_data = JSON.parse(event.data),
        type = event_data.type,
        client_id = event_data.client_id,
        client_display_name = event_data.client_display_name,
        client_image_url = event_data.client_image_url,
        text = event_data.text,
        stream_clients = event_data.stream_clients;

    message = '';
    switch(type) {
        case 'system_message':
            message = Constants.CONNECTED;
            console.log(client_id + ': connected');
            break;

        case 'message':
            $('input[name="message"]').val('');
            if (event_data.comment_add_status == "OK") {
                message = text;
            } else if (event_data.comment_add_status == "ERROR-CHAT-PERMISSIONS"){
                $.magnificPopup.open({
                    items: {
                        src: '#popup-blocked-info',
                        type: 'inline',
                        preloader: false,
                        modal: true
                    }
                });
                $('.popup-blocked-info-close-icon').click(function () {
                    $('#popup-blocked-info').magnificPopup('close');
                });
            }
            break;

        case 'like':
            like_state = event_data.like_state;
            if (like_state == 1) {
                $('#like').attr('data-like-state', '1');
                message = Constants.LIKED_THIS;
            } else if (like_state == 0){
                $('#like').attr('data-like-state', '0');
            }
            break;

        case 'follow':
            follow_state = event_data.follow_state;
            follow_icon_title = event_data.follow_icon_title;
            if (follow_state == 1) {
                message = Constants.FOLLOWS + ' <span>' + STREAM_DATA.hero_display_name + '<span>';
            }

            $('#follow').attr('title', follow_icon_title).attr('data-following-state', follow_state);
            $('#follow span').html(follow_icon_title);
            break;

        case 'block':
            block_state = event_data.block_state;
            block_icon_title = event_data.block_icon_title;
            blocked_user_id = event_data.blocked_user_id;
            if (block_state == 1) {
                message = '<span>' + Constants.BLOCKED + '</span>';
            }
            $('.block_user[data-user-id="'+ blocked_user_id +'"]').attr({'data-blocked-state': block_state,'title': block_icon_title});
            break;

        case 'loc':
            lat = event_data.lat;
            lng = event_data.lng;
            break;

        case 'ori':
            ori = event_data.ori;
            var box = (navigator.appName.indexOf('Microsoft')!=-1 ? window : document)['player'];
            box.changeOrientation(ori);
            console.log('orientation: ' +  ori);
            break;

        case 'heading':
            heading = event_data.heading;
            console.log('heading: ' +  heading);
            break;
    }

    if (message != '') {
        if (STREAM_DATA.admin_permissions && client_id != STREAM_DATA.hero_id) {

            line = '<div class="line" data-line-id=""><div class="comment_autor_image"><div class="profile_image" style="background: url(' + client_image_url + ') 100% 100% no-repeat;  background-size: cover;"></div></div><div class="comment_message"><span>' + client_display_name + '</span> ' + message + '</div><div class="block_user" data-user-id="' + client_id + '" data-blocked-state="" data-toggle="tooltip" title=""><i class="fa fa-lock fa-lg"></i></div></div>';
        } else {
            line = '<div class="line" data-line-id=""><div class="comment_autor_image"><div class="profile_image" style="background: url(' + client_image_url + ') 100% 100% no-repeat;  background-size: cover;"></div></div><div class="comment_message"><span>' + client_display_name + '</span> ' + message + '</div></div>';
        }

        write_to_screen(line);
        if (STREAM_DATA.admin_permissions) {
            block_user_ws();
            if (type == 'block') {
                $('.block_user[data-user-id="'+ client_id +'"]').attr({'data-blocked-state': block_state,'title': block_icon_title});
            }
        }
    }

    console.log(event.data);
}

function ws_onerror(event) {
    websocket.close();
    console.log(event.data);
}

function write_to_screen(message) {
    $container = $('#chat_messages');
    $container.append(message);
    $container[0].scrollTop = $container[0].scrollHeight;
}

function change_embed_link(embed_width, embed_height){
    var auto_play = $('#auto_play').prop('checked'),
        mute = $('#mute').prop('checked'),
        embed_url = '<iframe src="/api/v1/player?stream_uuid=' + STREAM_DATA.stream_uuid + '&width=' + embed_width + "&height=" + embed_height + "&auto_play=" + auto_play + "&mute=" + mute + '" width="' + embed_width + '" height="' + embed_height + '" frameborder="0" scrolling="no"></iframe>';
    $('#popup-embed .js-embed_url').val(embed_url);
}

function block_user_ws(){
    $('.block_user').unbind('click');
    $('.block_user').each(function() {
        $(this).on('click', function(){
            var blocked_user_id = $(this).attr('data-user-id');

            var ws_data = {
                type: 'block',
                stream_uuid: STREAM_DATA.stream_uuid,
                lang: STREAM_DATA.lang,
                client_id: STREAM_DATA.client_id,
                blocked_user_id: blocked_user_id
            };

            websocket.send(JSON.stringify(ws_data));
        });
    });
}

$(window).resize(function(){
    wrap_resize();
});

/* ========= Slide Recorded Streams =========*/
$(document).ready(function() {
    var $streams_carousel = $('#streams-carousel');
    $streams_carousel.owlCarousel({
        items: 4,
        margin: 10,
        lazyLoad: true,
        nav: false
    });

    $('.recorded-streams .next').click(function(){
        $streams_carousel.trigger('next.owl.carousel');
    });
    $('.recorded-streams .prev').click(function(){
        $streams_carousel.trigger('prev.owl.carousel');
    });
});


function wrap_resize(){
    var $player_wrapper = $('.player_wrapper');
    $player_wrapper.height($player_wrapper.width()*0.6);

    if ($(window).width > 992) {
        var maxHeight = -1;
        var $block = $('.block');
        $block.each(function() {
            maxHeight = maxHeight > $(this).height() ? maxHeight : $(this).height();
        });

        $block.each(function() {
            $(this).height(maxHeight);
        });
    }
}