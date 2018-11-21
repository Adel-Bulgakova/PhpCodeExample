$(document).ready(function() {

    $('#followers').DataTable({
        data: PROFILE_DATA.followers,
        pageLength: 10,
        pagingType: 'full_numbers',
        lengthMenu: [ [10, 50, 100, -1], [10, 50, 100, "Все"] ],
        // order: [[0, 'asc']],
        // columnDefs: [{orderable: false, targets: [3]}],
        searching: false,
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

    $('#following').DataTable({
        data: PROFILE_DATA.following,
        pageLength: 10,
        pagingType: 'full_numbers',
        lengthMenu: [ [10, 50, 100, -1], [10, 50, 100, "Все"] ],
        // order: [[0, 'asc']],
        // columnDefs: [{orderable: false, targets: [3]}],
        searching: false,
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

    $('#blocked').DataTable({
        data: PROFILE_DATA.blocked,
        pageLength: 10,
        pagingType: 'full_numbers',
        lengthMenu: [ [10, 50, 100, -1], [10, 50, 100, "Все"] ],
        // order: [[0, 'asc']],
        // columnDefs: [{orderable: false, targets: [3]}],
        searching: false,
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

    $('#user_devices').DataTable({
        data: PROFILE_DATA.devices,
        pageLength: 10,
        pagingType: 'full_numbers',
        lengthMenu: [ [10, 50, 100, -1], [10, 50, 100, "Все"] ],
        order: [[0, 'asc']],
        columnDefs: [{orderable: false, targets: [3]}],
        searching: false,
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

    $('#streams_online').DataTable({
        data: PROFILE_DATA.streams_online,
        pageLength: 10,
        pagingType: 'full_numbers',
        lengthMenu: [ [10, 50, 100, -1], [10, 50, 100, "Все"] ],
        // order: [[0, 'asc']],
        // columnDefs: [{orderable: false, targets: [3]}],
        searching: false,
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

    $('#streams_archive').DataTable({
        data: PROFILE_DATA.streams_archive,
        pageLength: 10,
        pagingType: 'full_numbers',
        lengthMenu: [ [10, 50, 100, -1], [10, 50, 100, "Все"] ],
        // order: [[0, 'asc']],
        // columnDefs: [{orderable: false, targets: [3]}],
        searching: false,
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

    $('#streams_recent').DataTable({
        data: PROFILE_DATA.streams_recent,
        pageLength: 10,
        pagingType: 'full_numbers',
        lengthMenu: [ [10, 50, 100, -1], [10, 50, 100, "Все"] ],
        // order: [[0, 'asc']],
        // columnDefs: [{orderable: false, targets: [3]}],
        searching: false,
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

    $('a.stream_view').click(function (e) {
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