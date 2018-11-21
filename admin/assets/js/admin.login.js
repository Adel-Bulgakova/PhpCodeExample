/* ========= Admin Login Form =========*/

$(document).ready(function(){
	$('#loginform').validate({
		submitHandler: function(form) {
			$('button[type=submit]').attr('disabled', 'true');
			$.ajax({
				type: 'POST',
				dataType: 'json',
				url: 'index.php?route=proc_login',
				data:  $('#loginform').serialize(),
				success: function(data) {
					if (data['success'] != '') {
						window.location = '/admin/index.php?route=page_index';
					} else {
						$('#result_login').html('<div class="alert alert-danger" role="alert">'+data["danger"]+'</div>');
					}
				}
			});
		}
	});    
});		