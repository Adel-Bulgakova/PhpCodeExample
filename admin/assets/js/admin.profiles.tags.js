/* ========= Tags List Page =========*/

$(document).ready(function() {
	var table = $('#profiles_tags').DataTable({
		ajax: {
			type: 'POST',
			url: 'index.php?route=proc_jsondata_get',
			data: {'query': 'profiles_tags'},
			dataSrc: function (json) {
				if (json == 'error') {
					console.log('processing error');
					return false;
				} else {
					return json;
				}
			}
		},
		pageLength: 100,
		pagingType: 'full_numbers',
		lengthMenu: [ [50, 100, 300, -1], [50, 100, 300, "Все"] ],
		columnDefs: [{orderable: false, targets: [0,2]},
		    {width: '40%', targets: 0},
			{width: '30%', targets: 1},
			{width: '30%', targets: 2}
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

	$('#profiles_tags tbody').on('click', 'a', function (e) {
		e.preventDefault();
		var action = $(this).attr('data-action');
		var tag_id = $(this).attr('data-tag-id');
		$('#confirm').attr('data-action', action);
		$('#confirm').attr('data-tag-id', tag_id);

		if (action == 'disable') {
			var modal_html = 'Подтвердите запрет использования тега';
		} else if (action == 'enable') {
			var modal_html = 'Подтвердите разрешение использования тега';
		}
		//Текст в окне подтверждения действия
		$('.modal-body p').html(modal_html);
	});

	//Выполнение действия при подтверждении
	$('#confirm').click(function() {
		var action = $(this).attr('data-action');
		var tag_id = $(this).attr('data-tag-id');
		$('#confirm_modal').modal('hide');
		$.ajax({
			type: 'POST',
			dataType: 'json',
			url: 'index.php?route=proc_profiles_tags_actions',
			data: {action: action, tag_id: tag_id},
			success: function(data){
				console.log(data);
				table.ajax.reload();
			}
		});
	});

});
