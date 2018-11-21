/* ========= Test Tools Page =========*/

$(document).ready(function() {
	message = '<div align =\"center\"><span>Тестирование</span><i class=\"fa fa-spinner fa-spin fa-3x\"></i></div>';
	
	$('#test_ping').validate({
		submitHandler: function(form) {
			$('#test_ping button[type=submit]').prop('disabled', true);
			$('#result_ip').empty();
			$('#result_ip').append(message);
			$.ajax({
				type: 'GET',
				url: 'index.php?route=proc_test_tools_ping',
				data: $('#test_ping').serialize(),
				success: function(data) {			           		
					$('#result_ip').empty();
					$('#result_ip').append(data);
					$('#result_ip').css('height', '');
					wrap_resize();				
					$('#test_ping button[type=submit]').prop('disabled', false);
				},
			});
		},
		rules: {
			ip: {required: true}
		},
		messages: {
			ip: {required: 'Вы не ввели IP адрес камеры'}
		}
	});	
	
	$('#test_port').validate({
		submitHandler: function(form) {
			$('#test_port button[type=submit]').prop('disabled', true);
			$('#result_port').empty();
			$('#result_port').append(message);
			$.ajax({
				type: 'GET',
				url: 'index.php?route=proc_test_tools_ping',
				data: $('#test_port').serialize(),
				success: function(data){			           		
					$('#result_port').empty();
					$('#result_port').append(data);
					$('#result_port').css('height', '');
					wrap_resize();						
					$('#test_port button[type=submit]').prop('disabled', false);
				},
			});
		},
		rules: {
			ip: {required: true}
		},
		messages: {
			ip: {required: 'Вы не ввели IP адрес камеры'}
		}
	});
	
	wrap_resize();	
});

$(window).resize(function(){
	wrap_resize();		
});

function wrap_resize(){
	var maxHeight = -1;
	$('.admin_panel_content').each(function() {
		maxHeight = maxHeight > $(this).height() ? maxHeight : $(this).height();
	});
	
	$('.admin_panel_content').each(function() {
		$(this).height(maxHeight);
	});
}