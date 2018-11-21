/* ========= Search, filters by tags, load =========*/

// Первая страница была загружена
var number_of_next_streams_block = 2;
$("#view-more").click(function(){
    var win_top = $(window).scrollTop();
    var doc_height = $(document).height();
    var win_height = $(window).height();
    var scrolltrigger = 1;
    var _scroll = win_top/(doc_height-win_height);
    var $data = $('div.content > div.container > div.data');

    if (number_of_next_streams_block < total_streams_blocks_count) {
        jqxhr = $.ajax({
            type: 'GET',
            dataType: 'json',
            url: 'index.php?route=proc_search',
            data: {action: 'load', query: number_of_next_streams_block},
            beforeSend: function() {
                $('div.content > div.container').append('<div class=\"ajax-loader\"></div>');
                $("#view-more").hide();
            },
            success: function(data) {
                $('.ajax-loader').remove();
                $("#view-more").show();
                $data.append(data.data);
                search();
                filter_by_tag();
                get_clients_count();
                show_popup_profile();
            },
            error: function(xhr, status, error){
                console.log(error);
            }
        });
        jqxhr.done(function () {
            number_of_next_streams_block++;
        });
    } else {
        $("#view-more").hide();
    }
});


$(document).ready(function() {
    search();
    filter_by_tag();
});

function search() {
    $('#search_input').bind('keyup change', function(e) {
        var search_input = $('#search_input');
        var search_query = search_input.val();
        if (search_query != ''){
            $.ajax({
                type: 'GET',
                url: 'index.php?route=proc_search',
                data: {action: 'search', query: search_query},
                beforeSend: function() {
                    $('div.search_results').append('<div class=\"ajax-loader\"></div>');
                },
                success: function(data) {
                    $('.search_results').html(data);
                    $('.search_results').niceScroll({cursorcolor:'#80C242'});
                    filter_by_tag();
                    show_popup_profile();
                    console.log(data);
                },
                error: function(xhr, status, error){
                    console.log(error);
                    $('.search_results').html(Constants.ERROR_SEARCH_RESULTS);
                }
            });
        }
    });
}

function filter_by_tag() {
    $('.tag').click(function(e){
        e.preventDefault();
        $('.tag').removeClass('active');
        $(this).addClass('active');
        var tag_name = $(this).html();
        $('#search_input').val(tag_name);
        var tag_id = $(this).attr('data-tag-id');
        $.ajax({
            type: 'GET',
            url: 'index.php?route=proc_search',
            data: {action: 'filter_by_tag', query: tag_id},
            beforeSend: function() {
                $('div.content > div.container > div.data').html('<div class=\"ajax-loader\"></div>');
            },
            success: function(data) {
                $(".search_results").getNiceScroll().remove();
                $('.data').html(data);
                get_clients_count();
                show_popup_profile();
                search();
            },
            error: function(xhr, status, error){
                console.log(error);
                $('.data').html(Constants.NO_STREAMS_BY_TAG);
            }
        });
    });
}