/* ========= Users List Page =========*/

$(document).ready(function() {
	var table = $('#users').DataTable({
		ajax: {
			type: 'POST',
			url: 'index.php?route=proc_jsondata_get',
			data: {'query': 'users_all'},
			dataSrc: function (json) {
				if (json == 'error') {
					console.log('processing error');
					return false;
				} else {
					return json;
				}
			}
		},
		pageLength: 300,
		pagingType: 'full_numbers',
		lengthMenu: [ [50, 100, 300, -1], [50, 100, 300, "Все"] ],
		order: [[0, 'asc']],
		columnDefs: [{orderable: false, targets: [3,5]}],
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

	$('#users tbody').on('click', 'a', function () {
		e.preventDefault();
		var action = $(this).attr('data-action');
		var user_id = $(this).attr('data-user-id');
		$('#confirm').attr('data-action', action);
		$('#confirm').attr('data-user-id', user_id);

		if (action == 'block') {
			var modal_html = 'Подтвердите блокировку пользователя';
		} else if (action == 'unblock') {
			var modal_html = 'Подтвердите разблокировку пользователя';
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
			url: 'index.php?route=proc_users_actions',
			data: {action: action, user_id: user_id},
			success: function(data){
				console.log(data);
				table.ajax.reload();
			}
		});
	});
});
