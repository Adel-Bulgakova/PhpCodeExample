/* ========= Official List Page =========*/

$(document).ready(function() {
	var table = $('#official').DataTable({
		ajax: {
			type: 'POST',
			url: 'index.php?route=proc_jsondata_get',
			data: {'query': 'users_official'},
			dataSrc: function (json) {
				if (json == 'error') {
					console.log('processing error');
					return false;
				} else {
					return json;
				}
			}
		},
		pageLength: 50,
		pagingType: 'full_numbers',
		lengthMenu: [ [50, 100, 300, -1], [50, 100, 300, "Все"] ],
		order: [[0, 'asc']],
		columnDefs: [{orderable: false, targets: [3,4,5]}],
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

	$('#official tbody').on('click', 'a', function (e) {
		e.preventDefault();
		var action = $(this).attr('data-action');
		var user_id = $(this).attr('data-user-id');

		$('#confirm').attr('data-action', action);
		$('#confirm').attr('data-user-id', user_id);
		if (action == 'check') {
			var modal_html = 'Подтвердите официальный источник.';
		} else if (action == 'cancel') {
			var modal_html = 'Подтвердите удаление пользователя из списка официальных источников.';
		}
		//Текст в окне подтверждения действия
		$('.modal-body p').html(modal_html);
	});

	//Выполнение действия при подтверждении
	$('#confirm').click(function() {
		var action = $(this).attr('data-action');
		var user_id = $(this).attr('data-user-id');
		$('#confirm_modal').modal('hide');
		$.ajax({
			type: 'POST',
			dataType: 'json',
			url: 'index.php?route=proc_official_actions',
			data: {action: action, user_id: user_id},
			success: function(data){
				console.log(data);
				table.ajax.reload();
			}
		});
	});

});
