$(document).ready(function(){
    $('.tabbable a').click(function(e) {
        e.preventDefault();
        $(this).tab('show');
    });

    $("ul.nav-pills > li > a").on("shown.bs.tab", function(e) {
        var id = $(e.target).attr("href").substr(1);
        window.location.hash = id;
    });

    var hash = window.location.hash;
    $('.tabbable a[href="' + hash + '"]').tab('show');

    var $official_checkbox = $('input[name="is_official"]');
    if (PROFILE_DATA.is_official == "1") {
        $official_checkbox.prop('checked', true);
    }

    $('#image_edit_icon').click(function(){
        $('.image_edit_result').empty();
        $('.image_cover').css('display', 'block');
    });

    var $file_input = $('input[type="file"]');
    $file_input.change(function (){
        var $image = $(this).val();
        $('.upload').css('display', 'none');
        var file_data = $file_input.prop('files')[0];
        var form_data = new FormData();
        form_data.append('profile_image', file_data);
        if ($image != '') {
            $.ajax({
                type: 'POST',
                dataType: 'json',
                url: 'index.php?route=proc_profile_image_tmp',
                data: form_data,
                cache: false,
                contentType: false,
                processData: false,
                success: function(data){

                    if (data.success == 'OK') {
                        var user_uuid = data.user_data;
                        $('.image_cover').css('display', 'none');
                        $('#profile_image_edit .profile_image').css({"background-image": "url('/users/tmp_images/" + user_uuid + ".jpg?" + new Date().getTime() + "')", "background-position": "100% 100%",  "background-repeat": "no-repeat", "background-size": "cover"});
                        $('input[name="is_image_changed"]').val(1);
                    } else {
                        $('.image_edit_result').html(data.danger);
                    }
                },
                error: function(xhr, status, error){
                    console.log(error);
                }
            });
        }
    });

    $('#profile_edit_form').validate({
        submitHandler: function(form) {
            $('#result_profile_edit').empty();
            $('#profile_edit_form input[type=submit]').attr('disabled', 'true');
            $.ajax({
                type: 'POST',
                dataType: 'json',
                url: 'index.php?route=proc_profile_edit',
                data: $('#profile_edit_form').serialize(),
                success: function(data){
                    if (data.status == 'OK'){
                        $('#result_profile_edit').html(data.message);
                    } else {
                        $.each(data.message, function(key, value) {
                            $('#result_profile_edit').append('<div class="alert alert-danger" role="alert">' + value + '</div>');
                        });
                    }
                    $('#profile_edit_form input[type=submit]').removeAttr('disabled');
                },
                error: function(xhr, status, error){
                    console.log(error);
                }
            });
        },
        rules: {
            login: {required: true}
        },
        messages: {
            login: {required: Constants.ERR_FILL_LOGIN}
        }
    });

    $('#search_query_remove').click(function () {
        $('#search_input').val('');
        get_chats();
        console.log('test click');
    });

    $(window).resize(function() {
        tabcontent_resize();
    });

    tabcontent_resize();
    get_following();
    get_followers();
    get_blacklist();
    get_streams();
    get_chats();
    get_devices();
    logout_all_devices();
    search_chats_by_query();
});

function tabcontent_resize(){
    var maxHeight = -1;

    $('.data').each(function() {
        maxHeight = maxHeight > $(this).height() ? maxHeight : $(this).height();
    });

    $('.data').each(function() {
        $(this).css('min-height', maxHeight);
    });
}

function get_following() {
    $.ajax({
        type: 'GET',
        dataType: 'text',
        url: 'index.php?route=proc_following_list',
        success: function(data){
            $('#following .data').html(data);
            show_popup_profile();
            change_following_state();
        },
        error: function(xhr, status, error){
            console.log(error);
            $('#following .data').html(Constants.REQUEST_FAILED_INFO);
        }
    });
}

function get_followers() {
    $.ajax({
        type: 'GET',
        dataType: 'text',
        url: 'index.php?route=proc_followers_list',
        success: function(data){
            $('#followers .data').html(data);
            show_popup_profile();
            change_following_state();
        },
        error: function(xhr, status, error){
            console.log(error);
            $('#followers .data').html(Constants.REQUEST_FAILED_INFO);
        }
    });
}

function get_blacklist() {
    $.ajax({
        type: 'GET',
        dataType: 'text',
        url: 'index.php?route=proc_blocked_list',
        success: function(data){
            $('#blacklist .data').html(data);
            show_popup_profile();
            change_block_state();
        },
        error: function(xhr, status, error){
            console.log(error);
            $('#blacklist .data').html(Constants.REQUEST_FAILED_INFO);
        }
    });
}

function get_streams() {
    $.ajax({
        type: 'GET',
        dataType: 'text',
        url: 'index.php?route=proc_streams_list',
        success: function(data){
            $('#streams .data').html(data);
            stream_edit_popup_open();
            stream_delete();
        },
        error: function(xhr, status, error){
            console.log(error);
            $('#streams .data').html(Constants.REQUEST_FAILED_INFO);
        }
    });
}

function get_chats() {
    $.ajax({
        type: 'GET',
        dataType: 'text',
        url: 'index.php?route=proc_chats_list',
        success: function(data){
            $('#chats .data').html(data);
            delete_chat();
        },
        error: function(xhr, status, error){
            console.log(error);
            $('#chats .data').html(Constants.REQUEST_FAILED_INFO);
        }
    });
}

function get_devices() {
    $.ajax({
        type: 'GET',
        dataType: 'text',
        url: 'index.php?route=proc_devices_list',
        success: function(data){
            $('#devices .data_devices').html(data);
            // device_edit_popup_open();
        },
        error: function(xhr, status, error){
            console.log(error);
            $('#devices .data_devices').html(Constants.REQUEST_FAILED_INFO);
        }
    });
}

function change_following_state() {
    $('.following_state_icon button').unbind('click');
    $('.following_state_icon button').each(function () {
        $(this).click(function() {
            var profile_id = $(this).parent().attr('data-profile-id');
            $.ajax({
                type: 'POST',
                dataType: 'json',
                url: 'index.php?route=proc_follow',
                data: {hero_id: profile_id},
                success: function (data) {
                    if (data.status == 'OK') {
                        if (data.state == '1') {
                            $('.following_state_icon[data-profile-id="'+ profile_id +'"]').html('<button class="btn theme-button">' + data.title + '</button>');
                        } else if (data.state == '0'){
                            $('.following_state_icon[data-profile-id="'+ profile_id +'"]').html('<button class="btn btn-default">' + data.title + '</button>');
                        }
                        change_following_state();
                    }
                    console.log(data);
                },
                error: function (xhr, status, error) {
                    console.log(error);
                }
            });
        });
    });
}

function search_chats_by_query() {
    $('#search_input').bind('keyup change', function(e) {
        var search_input = $('#search_input');
        var search_query = search_input.val();
        if (search_query.length > 1){
            $.ajax({
                type: 'POST',
                dataType: 'text',
                url: 'index.php?route=proc_chats_list_filter',
                data: {query: search_query},
                beforeSend: function() {
                    $('div.search_results').append('<div class=\"ajax-loader\"></div>');
                },
                success: function(data) {
                    $('#chats .data').html(data);
                    console.log(data);
                },
                error: function(xhr, status, error){
                    console.log(error);
                    $('.search_results').html(Constants.ERROR_SEARCH_RESULTS);
                }
            });
        }
    });
}

function change_block_state() {
    $('.blocked_state button').each(function () {
        $(this).click(function() {
            var blocked_user_id = $(this).parent().attr('data-profile-id');
            $.ajax({
                type: 'POST',
                dataType: 'json',
                url: 'index.php?route=proc_block',
                data: {blocked_user_id: blocked_user_id},
                success: function (data) {
                    if (data.status == 'OK') {
                        if (data.state == '1') {
                            $('.blocked_state[data-profile-id="'+ blocked_user_id +'"]').html('<button class="btn theme-button">' + data.title + '</button>');
                        } else if (data.state == '0'){
                            $('.blocked_state[data-profile-id="'+ blocked_user_id +'"]').html('<button class="btn btn-default">' + data.title + '</button>');
                        }
                        change_block_state(blocked_user_id);
                    }
                    console.log(data);
                },
                error: function (xhr, status, error) {
                    console.log(error);
                }
            });
        });
    });
}

function delete_chat() {
    $('.chat_delete_icon').each(function () {
        var $chat_delete_icon = $(this);
        $chat_delete_icon.click(function() {
            var chat_id = $(this).attr('data-chat-id');
            console.log(chat_id);
            $.ajax({
                type: 'POST',
                dataType: 'json',
                url: 'index.php?route=proc_jsondata_get',
                data: {query: 'user_chat_hide', chat_id: chat_id},
                success: function (data) {
                    if (data.status == 'OK') {
                        $chat_delete_icon.closest('.chats_list_item').remove();
                    }
                    console.log(data);
                },
                error: function (xhr, status, error) {
                    console.log(error);
                }
            });
        });
    });
}

function profile_delete_popup_open(){
    $.magnificPopup.open({
        items: {
            src: '#popup-profile-delete',
            type: 'inline',
            preloader: false,
            modal: true
        }
    });
    $('.popup-profile-delete-close-icon').click(function () {
        $('#popup-profile-delete').magnificPopup('close');
    });
}

function logout_all_devices(){
    $('#logout_all_devices').click(function(){
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: 'index.php?route=proc_profile_actions',
            data: {action: 'logout_all_devices'},
            success: function (data) {
                window.location = '/';
            },
            error: function (xhr, status, error) {
                console.log(error);
            }
        });
    });
}

function stream_edit_popup_open(){
    $('button.stream_edit').click(function(e){
        e.stopPropagation();
        var stream_uuid = $(this).attr('data-stream-uuid');
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: 'index.php?route=proc_jsondata_get',
            data: {query: 'stream_data', stream_uuid: stream_uuid},
            success: function (stream_data) {
                console.log(stream_data);
                if (stream_data.status == 'OK') {
                    var stream_name = stream_data.data.name,
                        watch_permissions = stream_data.data.permissions,
                        chat_permissions = stream_data.data.chat_permissions,
                        lat = stream_data.data.lat,
                        lng = stream_data.data.lng;

                    $('#popup-stream-edit form').attr('data-stream-uuid', stream_uuid);
                    $('#popup-stream-edit input[name="stream_name"]').val(stream_name);

                    var $public_radio = $('#popup-stream-edit input[value="public"]'),
                        $private_radio = $('#popup-stream-edit input[value="private"]'),
                        $chat_permissions_checkbox = $('#popup-stream-edit input[name="chat_permissions"]'),
                        $on_map_checkbox = $('#popup-stream-edit input[name="on_map"]');

                    var mutual_following_id_array = JSON.stringify(PROFILE_DATA.mutual_following);
                    $.ajax({
                        type: 'POST',
                        dataType: 'json',
                        url : 'index.php?route=proc_jsondata_get',
                        data: {query: 'users_array_data', users_array_data: mutual_following_id_array},
                        success: function(data) {
                            var watchers_profiles = '';
                            if (data.length > 0) {
                                var watchers = [];
                                $.each(data, function(key, value){
                                    var user_id = parseInt(value.data.id),
                                        display_name = value.data.display_name,
                                        profile_image_url = value.data.profile_image,
                                        checked_state = '';
                                    if ($.inArray(user_id, watch_permissions) >  -1){
                                        watchers.push(user_id);
                                        checked_state = 'checked';
                                    }
                                    watchers_profiles += '<div class="stream_watcher"><div class="profile_info" data-profile-id="' + user_id + '"><div class="profile_image" style="background: url(' + profile_image_url + ') 100% 100% no-repeat;  background-size: cover;"></div>' + display_name + '</div><div class="watch_permissions_state_icon"><input type="checkbox" name="permissions[]" value="'+ user_id + '" ' + checked_state + '></div></div>';
                                });

                                if (watchers.length > 0) {
                                    $private_radio.prop({'checked': true});
                                    display_state = 'block';
                                } else {
                                    $public_radio.prop({'checked': true});
                                    display_state = 'none';
                                }
                                $('.stream_watchers').css('display', display_state).find('.stream_watchers_profiles').html(watchers_profiles);
                                show_popup_profile();
                            } else {
                                $public_radio.prop({'checked': true, 'disabled': true});
                                $('.stream_watchers').css('display', 'none');
                            }
                        }
                    });

                    if (chat_permissions == 1) {
                        $chat_permissions_checkbox.prop('checked', true);
                    } else {
                        $chat_permissions_checkbox.prop('checked', false);
                    }

                    if (lat != 0 && lng != 0) {
                       $on_map_checkbox.prop('checked', true);
                    } else {
                       $on_map_checkbox.prop('checked', false);
                    }

                    $('button#stream_delete').attr('data-stream-uuid', stream_uuid);

                    $.magnificPopup.open({
                        items: {
                            src: '#popup-stream-edit',
                            type: 'inline',
                            preloader: false,
                            modal: true
                        }
                    });
                    $('.popup-stream-edit-close-icon').click(function () {
                        $('#popup-stream-edit').magnificPopup('close');
                    });
                    watch_permissions_state_change();
                    stream_edit();
                } else {
                    console.log('ERROR');
                }
            },
            error: function (xhr, status, error) {
                console.log(error);
            }
        });
    });
}

function watch_permissions_state_change(){
    var $permissions_state_radio = $('input[type="radio"][name="stream_permissions_state"]');
    $permissions_state_radio.change(function() {
        if ($('[type="radio"][value="public"]').prop('checked')) {
            $('.stream_watchers').css('display', 'none');
        } else if ($('[type="radio"][value="private"]').prop('checked')) {
            $('.stream_watchers').css('display', 'block');
        }
    });
}

function stream_edit() {
    var $result_stream_edit = $('.result_stream_edit');
    $result_stream_edit.empty();
    $('form[name="stream_edit_from"]').each( function() {

        $(this).validate({
            submitHandler: function(currentForm){
                var stream_uuid = currentForm.attributes[4].value;
                $('form[data-stream-uuid="' + stream_uuid + '"] input[type=submit]').attr('disabled', 'true');
                var message = Constants.REQUEST_FAILED_INFO;
                $.ajax({
                    type: 'POST',
                    dataType: 'json',
                    url: 'index.php?route=proc_stream_edit',
                    data:  $('form[data-stream-uuid="' + stream_uuid + '"]').serialize() + '&stream_uuid=' + stream_uuid,
                    success: function(data) {
                        if (data.status == 'OK') {
                            $result_stream_edit.html('<div class="alert alert-success">' + Constants.REQUEST_SUCCESS_INFO + '</div>');
                            get_streams();
                        } else {
                            $result_stream_edit.html('<div class="alert alert-danger">' + message + '</div>');
                        }
                        $('form[data-stream-uuid="' + stream_uuid + '"] input[type=submit]').removeAttr('disabled');
                        console.log(data);
                    },
                    error: function(xhr, status, error){
                        $result_stream_edit.html(message);
                        console.log(error);
                    }
                });
            }
        });
    });
}

function stream_delete() {
    var $stream_delete_button = $('#stream_delete');
    var $stream_delete_confirm_button = $('#stream_delete_confirm');

    $stream_delete_button.unbind();
    $stream_delete_confirm_button.unbind();

    $stream_delete_button.click(function(e){
        e.stopPropagation();
        $('#popup-stream-edit').magnificPopup('close');
        $.magnificPopup.open({
            items: {
                src: '#popup-stream-delete',
                type: 'inline',
                preloader: false,
                modal: true
            }
        });


        var message = Constants.REQUEST_FAILED_INFO,
            stream_uuid = $(this).attr('data-stream-uuid');

        $stream_delete_confirm_button.click(function(event){
            event.stopPropagation();
            $.ajax({
                type: 'POST',
                dataType: 'json',
                url: 'index.php?route=proc_stream_delete',
                data: {stream_uuid: stream_uuid},
                success: function(data) {
                    if (data.status == "OK") {
                        $('#popup-stream-delete').magnificPopup('close');
                        get_streams();
                    } else if (data.status == "ERROR" || data.status == "NOT-FOUND"){
                        $('#popup-stream-delete .popup_content p').html(message);
                        $('#popup-stream-delete .popup_body_control').html('');
                    }
                    console.log(data);
                },
                error: function(xhr, status, error){
                    $('#popup-stream-delete .popup_content p').html(message);
                    console.log(error);
                }
            });
        });
        $('.popup-stream-delete-close-icon').click(function () {
            $('#popup-stream-delete').magnificPopup('close');
        });
    });

}