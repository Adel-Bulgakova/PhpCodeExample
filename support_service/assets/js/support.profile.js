$(document).ready(function(){
	$('#admin_profile_edit_form').validate({
		submitHandler: function(form) {
			$('#result_profile_edit').empty();
			$('#admin_profile_edit_form input[type=submit]').attr('disabled', 'true');
			$.ajax({
				type: 'POST',
				dataType: 'json',
				url: 'index.php?route=proc_profile_edit',
				data: $('#admin_profile_edit_form').serialize(),
				success: function(data){
					if (data.status == 'OK'){
						$('#result_profile_edit').html('<div class="alert alert-success" role="alert">' + data.message + '</div>');
					} else {
						$('#result_profile_edit').html('<div class="alert alert-danger" role="alert">' + data.message + '</div>');
					}
					$('#admin_profile_edit_form input[type=submit]').removeAttr('disabled');
				},
				error: function(xhr, status, error){
					console.log(error);
				}
			});
		},
		rules: {
			name: {required: true}
		},
		messages: {
			name: {required: 'Введите имя'}
		}
	});
});		