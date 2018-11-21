/* ========= Streams Categories Page =========*/

$(document).ready(function() {
	var table = $('#streams_categories').DataTable({
		ajax: {
			type: 'POST',
			url: 'index.php?route=proc_jsondata_get',
			data: {'query': 'streams_categories'},
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

	$('#streams_categories tbody').on('click', 'button', function () {
		var action = $(this).attr('data-action');
		var category_id = $(this).attr('data-categorу-id');
		$('#confirm').attr('data-action', action);
		$('#confirm').attr('data-categorу-id', category_id);

		if (action == 'delete') {
			var modal_html = 'Подтвердите удаление категории';
			$('.modal-body').html(modal_html);
		} else if (action == 'edit'){
			$.ajax({
				type: 'POST',
				dataType: 'text',
				url: 'index.php?route=proc_streams_category_detail',
				data: {category_id: category_id},
				success: function(data){
					$('.modal-title').html('Редактирование категории');
					$('.modal-body').html(data);
				}
			});
		}
	});

    //Выполнение действия при подтверждении
	$('#confirm').click(function() {
		var action = $(this).attr('data-action');
		var category_id = $(this).attr('data-categorу-id');
		if (action == 'edit') {
			$.ajax({
				type: 'POST',
				dataType: 'json',
				url: 'index.php?route=proc_streams_categories_actions',
				data: $('#category_edit_form').serialize()+ "&action=edit",
				success: function(data){
					if (data.status == 'ERROR') {
						$('#category_edit_result').html('<div class=\"alert alert-danger text-center\">' + data.message + '</div>');
					} else if (data.status == 'OK') {
						$('#category_edit_result').html('<div class=\"alert alert-success text-center\">' + data.message + '</div>');
					}
					table.ajax.reload();
				}
			});
		} else {
			$.ajax({
				type: 'POST',
				dataType: 'json',
				url: 'index.php?route=proc_streams_categories_actions',
				data: {action: action, category_id: category_id},
				success: function(data){
					if (data.status == 'ERROR') {
						var modal_html = '<div class="alert alert-danger text-center" role="alert">' + data.message + '</div>';
						$('.modal-body').html(modal_html);
					} else if (data.status == 'OK') {
						$('#confirm_modal').modal('hide');
						table.ajax.reload();
					}
				}
			});
		}
	});

	$('#category_add').validate({
		submitHandler: function(form) {
			$('#category_add_result').html('');
			$('button[type=submit]').attr('disabled', 'true');
			$.ajax({
				type: 'POST',
				dataType: 'json',
				url: 'index.php?route=proc_streams_categories_actions',
				data: $('#category_add').serialize()+ "&action=add",
				success: function(data){
					if (data.status == 'ERROR') {
						$('#category_add_result').html('<div class="alert alert-danger text-center" role="alert">' + data.message + '</div>');
					} else if (data.status == 'OK') {
						$('#category_add_result').html('<div class="alert alert-success text-center" role="alert">' + data.message + '</div>');
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
			name_ru: {required: true},
			name_en: {required: true}
		},
		messages: {
			name_ru: {required: 'Данное поле обязательно для заполнения'},
			name_en: {required: 'Данное поле обязательно для заполнения'}
		}
	});
});