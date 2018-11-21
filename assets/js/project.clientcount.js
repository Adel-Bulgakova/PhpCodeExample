$(document).ready(function(){
    get_clients_count();
});

var get_clients = setInterval(function() { get_clients_count(); }, 10000);
function get_clients_count() {
    $.ajax({
        type: 'POST',
        url: 'index.php?route=proc_jsondata_get',
        datatype: 'json',
        data: {'query': 'clients_count'},
        success: function (data) {
            $.each(data, function (key, value) {
                $('.stream_preview[data-stream="'+key+'"] .client_count_data > span').html(value);
            });
            console.log('clients_count_upd');
        },
        error: function(xhr, status, error){
            console.log(error);
            $('.stream_preview .client_count_data > span').html(0);
        }
    });
}


