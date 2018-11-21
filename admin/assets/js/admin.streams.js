/* ========= Streams List Page =========*/

$(document).ready(function() {
	var table = $('#streams').DataTable({
		ajax: {
			type: 'POST',
			url: 'index.php?route=proc_jsondata_get',
			data: {'query': 'streams_all'},
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
		//order: [[0, 'asc']],
		columnDefs: [{orderable: false, targets: [0, 6]}],
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

	var table_streams_excess = $('#streams_excess').DataTable({
		ajax: {
			type: 'POST',
			url: 'index.php?route=proc_jsondata_get',
			data: {'query': 'streams_excess'},
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
		//order: [[0, 'asc']],
		// columnDefs: [{orderable: false, targets: [0, 6]}],
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

	$('#streams tbody').on('click', 'a.stream_view', function(e) {
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

	$('#streams tbody').on('click', '.stream_action', function(e) {
		var action = $(this).attr('data-action');
		var stream_id = $(this).attr('data-stream-id');
		$('#confirm').attr('data-action', action);
		$('#confirm').attr('data-stream-id', stream_id);

		if (action == 'block') {
			var modal_html = 'Подтвердите блокировку устройства.';
		} else if (action == 'unblock') {
			var modal_html = 'Подтвердите разблокировку устройства.';
		} else if (action == 'notify'){
			var modal_html = 'Подтвердите отправку уведомления.';
		}

		//Текст в окне подтверждения действия
		$('.modal-title').html("Подтверждение действия");
		$('.modal-body').html('<p>' + modal_html + '</p>');
		$('.modal-footer').html('<button type="button" class="btn btn-default" data-dismiss="modal">Отменить</button><button type="button" class="btn btn-primary" id="confirm" data-action="' + action + '" data-stream-id="' + stream_id + '">Подтвердить</button>');

		$('#confirm_modal').on('shown.bs.modal', function() {
			//Продолжение действия при подтверждении
			$('#confirm').click(function() {

				var action = $(this).attr('data-action');
				var stream_id = $(this).attr('data-stream-id');
				$('#confirm_modal').modal('hide');
				$.ajax({
					type: 'POST',
					dataType: 'text',
					url: 'index.php?route=proc_streams_actions',
					data: {action: action, stream_id: stream_id},
					success: function(data){
						console.log(data);
						table.ajax.reload();
					}
				});
			});
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

function flashIsReady(stream_url){
	var obj = new Object();
	if (stream_url.indexOf('rtmp') >= 0) {
		obj.rtmp = [{'streamUrl': stream_url}];
	} else if (stream_url.indexOf('hls') >= 0) {
		obj.hls = [{'streamUrl': stream_url}];
	} else if (stream_url.indexOf('hds') >= 0){
		obj.hds = [{'streamUrl': stream_url}];
	} else {
		console.log('Url содержит недопустимый формат.');
	}
	var box = (navigator.appName.indexOf('Microsoft')!=-1 ? window : document)['player'];
	box.sendToActionScript(obj);
}