$(document).ready(function(){
    $('#send_code_form').validate({
        submitHandler: function(form) {
            $('#result_send_code').empty();
            $('input[type=submit]').attr('disabled', 'true');
            phone = $('.phone_code_select').val() + $('input[name="phone_number"]').val();
            phone = phone.replace(/\D/g,'');

            post_data = "phone=" + phone + "&action=send_code&lang="+Constants.LANG;

            $.ajax({
                type: 'POST',
                dataType: 'json',
                url: 'index.php?route=proc_auth',
                data:  post_data,
                success: function(data) {
                    if (data.status == 'OK'){
                        $('#check_code_form input[name="phone"]').val(phone);
                        $('#recovery_account_form input[name="phone"]').val(phone);
                        $('#send_code_form').hide();
                        $('#check_code_form').show();
                        send_new_code(post_data);
                    } else if (data.status == 'ERROR'){
                        $('#result_send_code').html(data.message);
                    }
                    $('input[type=submit]').removeAttr('disabled');
                    console.log(data);
                },
                error: function(xhr, status, error){
                    console.log(error);
                    $('#result_send_code').html(Constants.REQUEST_FAILED_INFO);
                    
                }
            });
        },
        rules: {
            phone_number: {
                required: true,
                checkNumberWithRuPhoneCode:true
            }
        },
        messages: {
            phone_number: {required: Constants.ENTER_PHONE_NUMBER}
        },
        errorPlacement: function(error, element) {
            error.insertAfter(element);
        }
    });

    $('#check_code_form').validate({
        submitHandler: function(form) {
            $('#result_check_code').empty();
            $('input[type=submit]').attr('disabled', 'true');
            $.ajax({
                type: 'POST',
                dataType: 'json',
                url: 'index.php?route=proc_auth',
                data:  $('#check_code_form').serialize()+ "&action=check_code",
                success: function(data) {
                    if (data.status == 'OK') {
                        window.location = '/';
                    } else if (data.status == 'ACC-DELETED'){
                        $('#check_code_form').hide();
                        $('#recovery_account_form').show();
                        $('#recovery_account_form input[name="user_id"]').val(data.user_id);
                        $('#recovery_account_form #result_recovery_account').html(data.message);
                        
                    } else {
                        $('#result_check_code').html(data.message);
                    }
                    $('input[type=submit]').removeAttr('disabled');
                    console.log(data);
                },
                error: function(xhr, status, error){
                    console.log(error);
                    $('#result_check_code').html(Constants.REQUEST_FAILED_INFO);
                }
            });
        },
        rules: {
            hash_code: {required: true}
        },
        messages: {
            hash_code: {required: Constants.ENTER_CODE_FROM_SMS}
        },
        errorPlacement: function(error, element) {
            error.insertAfter(element);
        }
    });

    $('#recovery_account_form').validate({
        submitHandler: function(form) {
            $('#result_recovery_account').empty();
            $('input[type=submit]').attr('disabled', 'true');
            $.ajax({
                type: 'POST',
                dataType: 'json',
                url: 'index.php?route=proc_auth',
                data:  $('#recovery_account_form').serialize()+ "&action=recovery_account",
                success: function(data) {
                    if (data.status == 'OK') {
                        window.location = '/';
                    } else if (data.status == 'ERROR'){
                        $('#result_recovery_account').html(data.message);
                    }
                    $('input[type=submit]').removeAttr('disabled');
                    console.log(data);
                },
                error: function(xhr, status, error){
                    console.log(error);
                    $('#result_recovery_account').html(Constants.REQUEST_FAILED_INFO);
                }
            });
        },
        errorPlacement: function(error, element) {
            error.insertAfter(element);
        }
    });

    $.getJSON('/assets/lib/countryPhones.json/countryPhones.json', function(data) {
        $.each(data, function( key, value ){
            $('.phone_code_select optgroup').append('<option value="' + value.phone_code + '">' + value.phone_code + ' ' +  value.country + '</option>');
        });
    });

    $.validator.addMethod('checkNumberWithRuPhoneCode', function (value, element, param) {
        $('#result_send_code').empty();
        if ($('.phone_code_select').val() == '+7') {
            var phone = value.replace(/\D/g,'');
            if (phone.length == 10){
                return true;
            } else {
                return false;
            }
        } else {
            return true;
        }
    }, Constants.ENTER_CORRECT_PHONE_NUMBER);

});

function check_phone_code(phone_code) {
    var $phone_number_input =  $('#send_code_form input[name="phone_number"]');
    if (phone_code == "+7") {
        $phone_number_input.mask("(999) 999-99-99");
        $phone_number_input.on("blur", function() {
            var last = $(this).val().substr( $(this).val().indexOf("-") + 1 );
            
            if( last.length == 3 ) {
                var move = $(this).val().substr( $(this).val().indexOf("-") - 1, 1 );
                var lastfour = move + last;

                var first = $(this).val().substr(0, 9);
                $(this).val( first + '-' + lastfour );
            }
        });
    } else {
        $phone_number_input.unmask();
    }
}

function phone_mask() {
    var $select_phone_code = $('.phone_code_select');
    var phone_code = $select_phone_code.val();
    check_phone_code(phone_code);
    $select_phone_code.change(function() {
        var phone_code = $select_phone_code.val();
        check_phone_code(phone_code);
    });
}

function send_new_code(post_data){
    $("#send_new_code").click(function (e) {
        e.preventDefault();
        $('#result_check_code').empty();
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: 'index.php?route=proc_auth_new',
            data:  post_data,
            success: function(data) {
                if (data.status == 'OK'){
                    $('#result_check_code').html(Constants.NEW_CODE_SENT);
                } else if (data.status == 'ERROR'){
                    $('#result_check_code').html(data.message);
                    
                }
                $('input[type=submit]').removeAttr('disabled');
                console.log(data);
            },
            error: function(xhr, status, error){
                console.log(error);
                $('#result_check_code').html(Constants.REQUEST_FAILED_INFO);
            }
        });
    });
}