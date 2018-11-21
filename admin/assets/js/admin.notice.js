/* ========= Notices Page =========*/

$(document).ready(function() {

	datetimepicker_settings();

	$('#notice_add').validate({
		submitHandler: function(form) {
			$('#notice_add_result').html('');
			$('button[type=submit]').attr('disabled', 'true');
			$.ajax({
				type: 'POST',
				dataType: 'json',
				url: 'index.php?route=proc_notice_actions',
				data: $('#notice_add').serialize()+ "&action=add",
				success: function(data){
					if (data.status == 'ERROR') {
						$('#notice_add_result').html('<div class="alert alert-danger text-center" role="alert">' + data.message + '</div>');
					} else if (data.status == 'OK') {
						$('#notice_add_result').html('<div class="alert alert-success text-center" role="alert">' + data.message + '</div>');

						setTimeout(function () {
							$('input[type=text]').val('');
						}, 5000);
					}

					table.ajax.reload();
					$('button[type=submit]').removeAttr('disabled');
					console.log(data);
				},
				error: function(xhr, status, error){
					console.log(error);
				}
			});
		},
		rules: {
			notice_text: {required: true},
			activated_date: {required: true},
			deactivated_date: {check_expiration_param: true}
		},
		messages: {
			notice_text: {required: 'Данное поле обязательно для заполнения'},
			activated_date: {required: 'Укажите дату начала активности уведомления'}
		}
	});

	var table = $('#notices').DataTable({
		ajax: {
			type: 'POST',
			url: 'index.php?route=proc_jsondata_get',
			data: {'query': 'notices'},
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

	$('#notices tbody').on('click', 'button', function () {
		var action = $(this).attr('data-action');
		var notice_id = $(this).attr('data-notice-id');
		var $confirm_btn = $('#confirm');
		var $modal_title = $('.modal-title');
		var $modal_body = $('.modal-body');
		$confirm_btn.attr('data-action', action);
		$confirm_btn.attr('data-notice-id', notice_id);

		switch (action) {
			case 'edit':
				$.ajax({
					type: 'POST',
					dataType: 'text',
					url: 'index.php?route=proc_notice_detail',
					data: {notice_id: notice_id},
					success: function(data){
						$modal_title.html('Редактирование уведомления');
						$modal_body.html(data);
					}
				});
				break;
			case 'delete':
				$modal_title.html('Удаление уведомления');
				$modal_body.html('Подтвердите удаление уведомления');
				break;
			case 'send':
				$modal_title.html('Отправка уведомления');
				$modal_body.html('Подтвердите отправку уведомления. В разработке');
				break;
		}

	});

	//Выполнение действия при подтверждении удаления уведомления
	$('#confirm').click(function() {
		var action = $(this).attr('data-action');
		var notice_id = $(this).attr('data-notice-id');
		if (action == 'edit') {
			$.ajax({
				type: 'POST',
				dataType: 'json',
				url: 'index.php?route=proc_notice_actions',
				data: $('#notice_edit_form').serialize()+ '&action=' + action,
				success: function(data){
					if (data.status == 'ERROR') {
						$('#notice_edit_result').html('<div class="alert alert-danger text-center" role="alert">' + data.message + '</div>');
					} else if (data.status == 'OK') {
						$('#notice_edit_result').html('<div class="alert alert-success text-center" role="alert">' + data.message + '</div>');
					}

					table.ajax.reload();
				}
			});
		} else {
			$.ajax({
				type: 'POST',
				dataType: 'json',
				url: 'index.php?route=proc_notice_actions',
				data: {action: action, notice_id: notice_id},
				success: function(data){
					if (data.status == 'ERROR') {
						$('.modal-body').append('<div class="alert alert-danger text-center" role="alert">' + data.message + '</div>');
					} else {
						if (action == 'delete') {
							$('#confirm_modal').modal('hide');
						} else {
							$('.modal-body').append('<div class="alert alert-success text-center" role="alert">' + data.message + '</div>');
						}
						table.ajax.reload();
					}
					console.log(data);
				}
			});
		}
	});

	$('#confirm_modal').on('shown.bs.modal', function() {
		datetimepicker_settings();
	});

});

$.validator.addMethod('check_expiration_param', function (value, element, param) {
	var valid = 0;
	if ($('#without_expiration_param').is(":checked") || value != '') {
		valid = 1;
	}
	return valid;
}, 'Укажите дату заверщения активности уведомления или выберите "Бессрочно"');


function datetimepicker_settings() {
	$('.activated_date').datetimepicker({
		format: 'DD.MM.YYYY HH:mm',
		defaultDate: new Date()
	});

	$('.deactivated_date').datetimepicker({
		format: 'DD.MM.YYYY HH:mm'
	});

	$('.without_expiration_param').change(function() {
		var $deactivated_date_input = $('.deactivated_date');
		if ($(this).is(":checked")) {
			$deactivated_date_input.val('');
			$deactivated_date_input.attr('readonly', 'true');
			$('label[for="deactivated_date"]').remove();
		} else {
			$deactivated_date_input.removeAttr('readonly');
		}
	});
}