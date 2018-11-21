$(document).ready(function(){
    var table = $('#messages').DataTable({
        ajax: {
            url: 'index.php?route=proc_messages_get',
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
        // order: [[3, 'asc']],
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
});
