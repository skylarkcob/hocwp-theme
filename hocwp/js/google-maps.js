jQuery(document).ready(function ($) {
    if ("function" !== typeof google.maps.LatLng) {
        return;
    }

    (function () {
        var googleMaps = $(".hocwp-theme.google-maps-marker");
        googleMaps.each(function () {
            var element = $(this),
                mapID = element.attr("id"),
                input = element.next("input"),
                latLng = new google.maps.LatLng(element.attr("data-latitude"), element.attr("data-longitude")),
                options = {
                    center: latLng,
                    zoom: parseInt(element.attr("data-zoom")),
                    scrollwheel: element.attr("data-scrollwheel")
                },
                map = new google.maps.Map(document.getElementById(mapID), options),
                marker = new google.maps.Marker({
                    position: latLng,
                    map: map,
                    draggable: true,
                    title: element.attr("data-marker-title")
                }),
                point = marker.getPosition();
            google.maps.event.addListener(marker, "dragend", function () {
                point = marker.getPosition();
                map.panTo(point);
                if (input.length) {
                    input.val(JSON.stringify(point));
                }
                element.attr("data-latitude", point.lat);
                element.attr("data-longitude", point.lng);
            });

            element.on("hocwpTheme:changeMapAddress", function (e, address) {
                if ($.trim(address)) {
                    var geocoder = new google.maps.Geocoder();
                    if (geocoder === null) {
                        geocoder = new google.maps.Geocoder();
                    }
                    geocoder.geocode({address: address}, function (results, status) {
                        if (status === google.maps.GeocoderStatus.OK) {
                            var bounds = results[0].geometry.bounds;
                            if (bounds) {
                                map.fitBounds(bounds);
                                map.setZoom(15);
                                latLng = new google.maps.LatLng(results[0].geometry.location.lat(), results[0].geometry.location.lng());
                                marker.setPosition(latLng);
                                point = marker.getPosition();
                                if (input.length) {
                                    input.val(JSON.stringify(point));
                                }
                                map.setCenter(point);
                                google.maps.event.addListener(marker, 'dragend', function (event) {
                                    point = marker.getPosition();
                                    map.panTo(point);
                                    if (input.length) {
                                        input.val(JSON.stringify(point));
                                    }
                                    element.attr("data-latitude", point.lat);
                                    element.attr("data-longitude", point.lng);
                                });
                            }
                        }
                    });
                }
            });

            var geoAddress = $("input[data-for-maps='" + mapID + "'], select[data-for-maps='" + mapID + "']");
            if (geoAddress.length) {
                geoAddress.on("change", function () {
                    var address = $(this).val();
                    element.trigger("hocwpTheme:changeMapAddress", [address]);
                });
            }
        });
    })();
});