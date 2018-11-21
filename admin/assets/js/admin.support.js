/* ========= Support Service Page =========*/

$(document).ready(function() {
	var table = $('#support_service').DataTable({
		ajax: {
			type: 'POST',
			url: 'index.php?route=proc_jsondata_get',
			data: {'query': 'support_service_admins'},
			dataSrc: function (json) {
				if (json == 'error') {
					console.log('processing error');
					return false;
				} else {
					return json;
				}
			}
		},
		pageLength: 25,
		pagingType: 'full_numbers',
		lengthMenu: [ [10, 25, 50, -1], [10, 25, 50, "Все"] ],
		order: [[0, 'asc']],
		columnDefs: [{orderable: false, targets: [0, 1, 4]}
		  ],
		language: {
			emptyTable: 'Нет данных для отображения',
			info: 'Страница _PAGE_ из _PAGES_',
			lengthMenu:  'Отобразить _MENU_ записей',
			search:  'Искать:',
			paginate: {
				first: 'В начало',
				last: 'В конец',
				next: '>>',
				previous: '<<'
			}							    
		}
	});

	$('#support_service tbody').on('click', 'button', function () {
		var action = $(this).attr('data-action');
		var admin_id = $(this).parent().attr('data-admin-id');
		$('#confirm').attr('data-action', action);
		$('#confirm').attr('data-admin-id', admin_id);

		if (action == 'delete') {
			var modal_html = 'Подтвердите удаление администратора службы поддержки';
			$('.modal-body').html(modal_html);
		} else if (action == 'edit'){
			$.ajax({
				type: 'POST',
				dataType: 'text',
				url: 'index.php?route=proc_admin_detail',
				data: {admin_id: admin_id},
				success: function(data){
					$('.modal-title').html('Редактирование администратора службы поддержки');
					$('.modal-body').html(data);
				}
			});
		}
	});

    //Выполнение действия при подтверждении удаления администратора
	$('#confirm').click(function() {
		var action = $(this).attr('data-action');
		var admin_id = $(this).attr('data-admin-id');
		if (action == 'edit') {
			$.ajax({
				type: 'POST',
				dataType: 'json',
				url: 'index.php?route=proc_admin_actions',
				data: $('#admin_edit_form').serialize()+ "&action=edit",
				success: function(data){
					if (data.danger == '' && data.success != '') {
						$('#admin_edit_result').html('<div class=\"alert alert-success text-center\">' + data.success + '</div>');
					} else {
						var alert = '';
						$.each(data.danger, function(key, value) {
							alert = alert + value + '<br>';
						});
						$('#admin_edit_result').html('<div class=\"alert alert-danger text-center\">' + alert + '</div>');
					}
					table.ajax.reload();
				}
			});
		} else {
			$('#confirm_modal').modal('hide');
			$.ajax({
				type: 'POST',
				dataType: 'json',
				url: 'index.php?route=proc_admin_actions',
				data: {action: action, admin_id: admin_id},
				success: function(data){
					console.log(data.html);
					table.ajax.reload();
				}
			});
		}
	});

	$('#generate').click(function(){
		var string = '';
		var characters = '0123456789abcdef';
		for (var i =0; i < 12; i++ ) {
			string += characters.charAt(Math.floor(Math.random() * characters.length));
		}
		$('input[name="password"]').val(string);
	});

	$('#admin_add').validate({
		submitHandler: function(form) {
			$('#admin_add_result').html('');
			$('button[type=submit]').attr('disabled', 'true');
			$.ajax({
				type: 'POST',
				dataType: 'json',
				url: 'index.php?route=proc_admin_actions',
				data: $('#admin_add').serialize()+ "&action=add",
				success: function(data){
					if (data.danger == '' && data.success != '') {
						$('#admin_add_result').html('<div class=\"alert alert-success text-center\">' + data.success + '</div>');
					} else {
						var alert = '';
						$.each(data.danger, function(key, value) {
							alert = alert + value + '<br>';
						});
						$('#admin_add_result').html('<div class=\"alert alert-danger text-center\">' + alert + '</div>');
					}
					table.ajax.reload();
					$('button[type=submit]').removeAttr('disabled');
				},
				error: function(xhr, status, error){
					console.log(error);
				}
			});
		},
		rules: {
			admin_login: {required: true},
			admin_name: {required: true},
			password: {required: true}
		},
		messages: {
			admin_login: {required: 'Данное поле обязательно для заполнения'},
			admin_name: {required: 'Данное поле обязательно для заполнения'},
			password: {required: 'Данное поле обязательно для заполнения'}
		}
	});
});