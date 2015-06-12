'use strict';
// This module should contain "global" code, like jQuery plugin calls.
// Code placed here is different than the code placed in the shame.js module,
// since although the code written here is not modular, it is not shameful
// either. It's just the way it works.
var $ = require('jquery');
var eventEnd = require('../module/event_end');

module.exports = {
    center: {
        lat: 46.680999,
        lng: 21.088143
    },
    markerPos: {
        lat: 46.680999,
        lng: 21.088143
    },
    zoom: 15,

    init: function() {
        if (!$('#js-map').length) {
            return;
        }

        var centerLatLng = new google.maps.LatLng(
            this.center.lat,
            this.center.lng
        );
        var markerLatLng = new google.maps.LatLng(
            this.markerPos.lat,
            this.markerPos.lng
        );

        var mapOptions = {
            zoom: this.zoom,
            center: centerLatLng,
            mapTypeId: google.maps.MapTypeId.ROADMAP,
            disableDefaultUI: true,
            scrollwheel: false,
        };

        var map = new google.maps.Map(document.getElementById('js-map'), mapOptions);

        try {
            var Marker = new google.maps.Marker({
                position: markerLatLng,
                map: map
            });
        } catch(e) {}

        eventEnd.onResizeEnd(
            'maps',
            function () {
                map.setCenter(centerLatLng);
            },
            this
        );
    }
};
