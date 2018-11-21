var gm = google.maps;
var config = {
    el: 'map_canvas',
    lat: 45.1332,
    lon: 36.9336,
    zoom: 3,
    type: google.maps.MapTypeId.ROADMAP,
    minZoom: 15
};
var spiderConfig = {
    keepSpiderfied: true,
    event: 'mouseover'
};
var clusterOptions = {
    zoomOnClick: true,
    styles: [
        {height: 50, textColor: 'white', url: '/assets/images/icons/marker_50.svg', width: 50},
        {height: 56, textColor: 'white', url: '/assets/images/icons/marker_56.svg', width: 56},
        {height: 66, textColor: 'white', url: '/assets/images/icons/marker_66.svg', width: 66},
        {height: 78, textColor: 'white', url: '/assets/images/icons/marker_78.svg', width: 78},
        {height: 90, textColor: 'white', url: '/assets/images/icons/marker_90.svg', width: 90}
    ]
};

function initialize() {
    jqxhr = $.ajax({
        type: 'GET',
        dataType: 'json',
        url: 'index.php?route=proc_markers_get',
        success: function (data) {
            console.log(data);
        }
    });
    jqxhr.done(function (data) {

        var map = new gm.Map(document.getElementById(config.el), {
            minZoom: 2,
            zoom: config.zoom,
            center: new gm.LatLng(config.lat, config.lon),
            mapTypeId: config.type
        });

        var markerSpiderfier = new OverlappingMarkerSpiderfier(map, spiderConfig);
        var markers = [];

        $.each(data, function(key, value) {
            var loc = new gm.LatLng(parseFloat(value.lat), parseFloat(value.lng));
            var stream_status = value.stream_status;
            var profile_id = value.profile_id;
            var profile_image_url = value.profile_image_url;
            var profile_name = value.profile_name;
            var stream_uuid = value.stream_uuid;
            var stream_name = value.stream_name;
            var snapshot_url = value.snapshot_url;

            var content = '<div class=\"marker_content stream_preview\"><div class=\"marker_header profile_info\" data-profile-id=\"' + profile_id + '\"><div class=\"profile_image\" style=\"background: url(' + profile_image_url + ') 100% 100% no-repeat;  background-size: cover;\"></div><div class=\"profile_name\">' + profile_name + '</div></div><a href=\"/index.php?route=page_play&user=' + profile_id + '&uuid=' + stream_uuid + '\"><div class=\"thumbnail\" style=\"background: url(' + snapshot_url + ') center center no-repeat; background-size: cover;\"><i class=\"icon-play\"></i></div></a><div class=\"marker_footer\">' + stream_name + '</div></div>';

            var marker = new gm.Marker({
                icon: '/assets/images/icons/marker.png',
                position: loc,
                title: content,
                map: map
            });
            marker.desc = profile_name + ' in PROGECT_NAME';
            markers.push(marker);
            markerSpiderfier.addMarker(marker);
        });

        var iw = new gm.InfoWindow();
        markerSpiderfier.addListener('click', function (marker, e) {
            iw.setContent(marker.title);
            iw.open(map, marker);
        });

        markerSpiderfier.addListener('spiderfy', function (markers) {
            iw.close();
        });

        var markerCluster = new MarkerClusterer(map, markers, clusterOptions);
        markerCluster.setMaxZoom(config.minZoom);
    });
}

gm.event.addDomListener(window, 'load', initialize);