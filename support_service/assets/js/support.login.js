/* ========= Admin Login Form =========*/

$(document).ready(function(){
	$('#loginform').validate({
		submitHandler: function(form) {
			$('button[type=submit]').attr('disabled', 'true');
			$.ajax({
				type: 'POST',
				dataType: 'json',
				url: '/support_service/index.php?route=proc_login',
				data:  $('#loginform').serialize(),
				success: function(data) {
					if (data['success'] != '') {
						window.location = '/support_service/index.php?route=page_archive_chats';
					} else {
						$('#result_login').html('<div class="alert alert-danger" role="alert">'+data["danger"]+'</div>');
					}
				}	
			});
		}
	});    
});		