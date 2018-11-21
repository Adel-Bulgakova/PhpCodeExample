/* ========= Support Service Page =========*/

$(document).ready(function() {
		var admin_archive_chats = $('#archive_chats').DataTable({
		ajax: {
			type: 'POST',
			url: 'index.php?route=proc_jsondata_get',
			data: {'query': 'support_service_archive_chats', 'support_admin_id': ADMIN_DATA.admin_id},
			dataSrc: function (json) {
				if (json == 'error') {
					console.log('processing error');
					return false;
				} else {
					return json;
				}
			}
		},
		fnCreatedRow: function(nRow, aData, iDataIndex) {
			$(nRow).attr({'data-toggle':'modal','data-target':'.bs-modal-lg'});
		},
		pageLength: 300,
		pagingType: 'full_numbers',
		lengthMenu: [ [50, 100, 300, -1], [50, 100, 300, "Все"] ],
		order: [[3, 'asc']],
		columnDefs: [{orderable: false, targets: [1]}
			//{width: '20%', targets: 0 }
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

	$('#archive_chats tbody').on('click', 'tr', function () {
		if ($(this).hasClass('selected') ) {
			$(this).removeClass('selected');
		} else {
			admin_archive_chats.$('tr.selected').removeClass('selected');
			$(this).addClass('selected');
			var chat_id = $(this).find('.chat_id').attr('data-chat-id');
			modal_chat_view(chat_id);
		}
	});
});

function modal_chat_view(chat_id){
	$.ajax({
		type: 'POST',
		dataType: 'html',
		url: 'index.php?route=proc_support_service_admin_chat',
		data: {chat_id: chat_id},
		success: function(data){
			$('.modal-title').html('Чат №' + chat_id );
			$('.modal-body').html(data);
			$('.modal-footer').empty();
			console.log(data);
		},
		error: function(xhr, status, error){
			console.log(error);
		}
	});

}

