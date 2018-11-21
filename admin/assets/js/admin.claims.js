/* ========= Claims List Page =========*/

$(document).ready(function() {
	var table = $('#claims').DataTable({
		ajax: {
			type: 'POST',
			url: 'index.php?route=proc_jsondata_get',
			data: {'query': 'claims_on_streams'},
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
		order: [[3, 'asc']],
		columnDefs: [{orderable: false, targets: [0,2]}
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

	$('#claims tbody').on('click', 'a', function (e) {
		e.preventDefault();
		var action = $(this).attr('data-action');
		var claim_id = $(this).closest('div.actions').attr('data-claim-id');
		var stream_id = $(this).closest('div.actions').attr('data-stream-id');
		$('#confirm').attr('data-action', action);
		$('#confirm').attr('data-claim-id', claim_id);
		$('#confirm').attr('data-stream-id', stream_id);

		if (action == 'block') {
			var modal_html = 'Подтвердите блокировку устройства';
		} else if (action == 'reject') {
			var modal_html = 'Подтвердите проверку устройства и отклонение жалобы';
		} else if (action == 'delete'){
			var modal_html = 'Подтвердите удаление жалобы';
		}
		//Текст в окне подтверждения действия
		$('.modal-body p').html(modal_html);

	});

	$('#claims tbody').on('click', 'a.stream_view', function(e) {
		e.preventDefault();
		var stream_uuid = $(this).attr('data-stream-uuid');
		var stream_url = $(this).attr('data-stream-url');
		var stream_name = $(this).attr('data-stream-name');
		modal_stream_view(stream_uuid, stream_url, stream_name, "screen");

		$('.modal-footer').css('text-align', 'center').html('<a href="#" id="screen" title="Просмотр скриншота"><i class="fa fa-image fa-3x"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="#" id="play" title="Online просмотр"><i class="fa fa-play-circle fa-3x"></i></a>');

		$('#screen').click(function(e) {
			e.preventDefault();
			modal_stream_view(stream_uuid, stream_url, stream_name, "screen");
		});

		$('#play').click(function(e) {
			e.preventDefault();
			modal_stream_view(stream_uuid, stream_url, stream_name, "play");
		});

	});

	//Выполнение действия при подтверждении
	$('#confirm').click(function() {
		var action = $(this).attr('data-action');
		var claim_id = $(this).attr('data-claim-id');
		var stream_id = $(this).attr('data-stream-id');
		$('#confirm_modal').modal('hide');
		$.ajax({
			type: 'POST',
			dataType: 'json',
			url: 'index.php?route=proc_claims_actions',
			data: {action: action, claim_id: claim_id, stream_id: stream_id},
			success: function(data){
				console.log(data);
				table.ajax.reload();
			}
		});
	});

});

function modal_stream_view(stream_uuid, stream_url, stream_name, view){
	if (view == "screen") {
		$('.modal-title').html('Просмотр скриншота');
		$('.modal-body').html("<h4>" + stream_name + "</h4><br><div class=\"modal-screenshot\" style=\"background: url('/api/v1/streams/snapshot/" + stream_uuid + "') center center no-repeat;  background-size: cover;\"></div>");
		var width = $('.modal-screenshot').width();
		var height = width*0.6;
		$('.modal-screenshot').height(height);
	} else if (view == "play") {
		$('.modal-title').html('Online просмотp');
		$('.modal-body').html('<h4>' + stream_name + '</h4><br><div class="modal-player"><object width=\"100%\" height=\"100%\" codebase=\"http://fpdownload.macromedia.com/get/flashplayer/current/swflash.cab\"><param name=\"movie\" value=\"/admin/player/stream_player.swf\"/><param name=\"bgcolor\" value=\"#000\" /><param name=\"allowFullScreen\" value=\"true\"/><param name=\"wmode\" value=\"opaque\"><param name=\"allowScriptAccess\" value=\"sameDomain\" /><param name=\"flashvars\" value=\"stream_url=' + stream_url + '&scaleMode=letterbox&xml_url=/admin/player/params.xml\" /><embed src=\"/admin/player/stream_player.swf\" type=\"application/x-shockwave-flash\" bgcolor=\"#000\" wmode=\"direct\" loop=\"false\" quality=\"high\" allowScriptAccess=\"sameDomain\" allowFullScreen=\"true\" flashvars=\"stream_url=' + stream_url + '&scaleMode=letterbox&xml_url=/admin/player/params.xml\" width=\"100%\" height=\"100%\" pluginspage=\"http://www.macromedia.com/go/getflashplayer\"></object></div>');
		var width = $('.modal-player').width();
		var height = width*0.6;
		$('.modal-player').height(height);
	}
}