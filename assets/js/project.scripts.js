/* ========= Scripts =========*/
$(document).ready(function(){
    //Активный элемент меню
    var url = window.location.search;
    $('nav a').each(function() {
        var href = $(this).attr('href');
        var page = href.substring(href.indexOf('=')+1, href.length);
        if (url.indexOf(page) != -1){
            $('nav a').not($(this)).parent('li').removeClass('active');
            $(this).parent('li').addClass('active');
        }
    });


    //Scroll to top
    var $to_top = $('.scroll_to_top');
    $(window).scroll(function(){
        if ($(this).scrollTop() != 0) {
            $to_top.fadeIn();
        } else {
            $to_top.fadeOut();
        }
    });

    $to_top.click(function(event){
        event.preventDefault();
        $('body,html').animate({
                scrollTop: 0
            }, 700
        );
    });

    preview_streams_resize();
    search_results_resize();
    show_popup_profile();
    top_banners_display(); /* ========= Slide Banners =========*/
});

$(window).resize(function(){
    preview_streams_resize();
    search_results_resize();
    top_banners_display(); /* ========= Slide Banners =========*/
});

//Выравнивание высоты превью блоков трансляций
function preview_streams_resize(){
    var maxHeight = -1;
    $('.preview').each(function() {
        maxHeight = maxHeight > $(this).height() ? maxHeight : $(this).height();
    });

    $('.preview').each(function() {
        $(this).height(maxHeight);
    });
}

//Выравнивание ширины блока с выводом результата поиска
function search_results_resize(){
    var block_width = $('#search_input').width();
    $('.search_results').width(block_width);
}


function top_banners_display(){
    var $window_width = $(window).width(),
        owlCarouselOptions = {
            items:1,
            lazyLoad:true,
            loop:true,
            margin:0,
            nav:true,
            smartSpeed: 1500,
            autoplay: true,
            autoplayTimeout: 7000,
            autoplayHoverPause: true,
            navClass: ["banner_slider_arrow_left","banner_slider_arrow_right"]
        };

    if ($window_width > 1200) {
        $('.banners-container-1200').css('display', 'block').owlCarousel(owlCarouselOptions);
        $('div[class^="banners-container-"]').not('.banners-container-1200').css('display', 'none');
    } else if ($window_width > 992 && $window_width < 1200){
        $('.banners-container-1024').css('display', 'block').owlCarousel(owlCarouselOptions);
        $('div[class^="banners-container-"]').not('.banners-container-1024').css('display', 'none');
    } else if ($window_width > 776 && $window_width < 991){
        $('.banners-container-991').css('display', 'block').owlCarousel(owlCarouselOptions);
        $('div[class^="banners-container-"]').not('.banners-container-991').css('display', 'none');
    } else {
        $('.banners-container-775').css('display', 'block').owlCarousel(owlCarouselOptions);
        $('div[class^="banners-container-"]').not('.banners-container-775').css('display', 'none');
    }
    $('.banner_slider_arrow_left').html('<div class="banner_slider_arrow_pointer"></div>');
    $('.banner_slider_arrow_right').html('<div class="banner_slider_arrow_pointer"></div>');
}


/* ========= Slide Top Streams =========*/
$(document).ready(function() {
    var $top_carousel = $('#top-carousel');

    $top_carousel.owlCarousel({
        items: 4,
        margin: 10,
        loop: true,
        lazyLoad: true,
        nav: false
    });

    $('.top_streams .next').click(function(){
        $top_carousel.trigger('next.owl.carousel');
    });
    $('.top_streams .prev').click(function(){
        $top_carousel.trigger('prev.owl.carousel');
    });


    /* ========= Slide Official Streams =========*/
    var $official_carousel = $('#official-carousel');
    $official_carousel.owlCarousel({
        items: 4,
        margin: 10,
        loop: true,
        lazyLoad: true,
        nav: false
    });

    $('.official_streams .next').click(function(){
        $official_carousel.trigger('next.owl.carousel');
    });
    $('.official_streams .prev').click(function(){
        $official_carousel.trigger('prev.owl.carousel');
    });

});

//Модальное окно с детальной информацией профиля при клике на фото профиля
function show_popup_profile() {
    $('.profile_info').each(function () {
        $(this).click(function () {
            var profile_id = $(this).attr('data-profile-id');
            $.ajax({
                type: 'GET',
                url: 'index.php?route=proc_profile_get',
                data: {profile_id: profile_id},
                success: function (html) {
                    $('#popup-profile .popup_content').html(html);
                    $.magnificPopup.open({
                        items: {
                            src: '#popup-profile',
                            type: 'inline',
                            preloader: false,
                            modal: true
                        }
                    });
                    $('.popup-profile-close-icon').click(function () {
                        $('#popup-profile').magnificPopup('close');
                    });
                    change_following_state_on_profile_view();
                    get_clients_count();
                },
                error: function (xhr, status, error) {
                    console.log(error);
                }
            });
        });
    });
}

function change_following_state_on_profile_view() {
    var $button_follow = $('button#follow');
    $button_follow.unbind('click');
    $button_follow.click(function() {
        var profile_id = $(this).attr('data-profile-id');
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: 'index.php?route=proc_follow',
            data: {hero_id: profile_id},
            success: function (data) {
                if (data.status == 'OK') {
                    $button_follow.attr({'data-following-state':data.state, title:data.title}).html('<span>' + data.title + '</span> <i class=\"fa fa-star\"></i>');
                }
                change_following_state_on_profile_view();
                console.log(data);
            },
            error: function (xhr, status, error) {
                console.log(error);
            }
        });
    });
}


function block_user(stream_id){
    $('.block_user').unbind('click');
    $('.block_user').each(function() {
        $(this).on('click', function(){
            var user_id = $(this).attr('data-user-id');
            $.ajax({
                type: 'POST',
                dataType: 'json',
                url: 'index.php?route=proc_block',
                data: {stream_uuid: stream_uuid, user_id: user_id},
                success: function(data){
                    if (data.status = "OK") {
                        if (data.state == '0') {
                            $('.block_user[data-user-id="'+ user_id +'"]').attr('data-blocked-state', '0');
                        } else if (data.state == '1'){
                            $('.block_user[data-user-id="'+ user_id +'"]').attr({'data-blocked-state': '1','title': data.title});
                        }
                    } else {
                        console.log(data.message);
                    }
                },
                error: function(xhr, status, error){
                    console.log(error);
                }
            });
        });
    });
}

