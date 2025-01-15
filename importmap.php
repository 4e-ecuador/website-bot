<?php

/**
 * Returns the importmap for this application.
 *
 * - "path" is a path inside the asset mapper system. Use the
 *     "debug:asset-map" command to see the full list of paths.
 *
 * - "entrypoint" (JavaScript only) set to true for any module that will
 *     be used as an "entrypoint" (and passed to the importmap() Twig function).
 *
 * The "importmap:require" command can be used to add new entries to this file.
 */
return [
    'app' => [
        'path' => './assets/app.js',
        'entrypoint' => true,
    ],
    '@symfony/stimulus-bundle' => [
        'path' => './vendor/symfony/stimulus-bundle/assets/dist/loader.js',
    ],
    '@hotwired/stimulus' => [
        'version' => '3.2.2',
    ],
    'bootstrap' => [
        'version' => '5.3.3',
    ],
    '@popperjs/core' => [
        'version' => '2.11.8',
    ],
    'bootstrap/dist/css/bootstrap.min.css' => [
        'version' => '5.3.3',
        'type' => 'css',
    ],
    'stimulus-use' => [
        'version' => '0.52.2',
    ],
    'js-datepicker' => [
        'version' => '5.18.3',
    ],
    'js-datepicker/dist/datepicker.min.css' => [
        'version' => '5.18.3',
        'type' => 'css',
    ],
    'open-iconic/font/css/open-iconic-bootstrap.css' => [
        'version' => '1.1.1',
        'type' => 'css',
    ],
    'fullcalendar' => [
        'version' => '5.11.5',
    ],
    '@fullcalendar/core' => [
        'version' => '5.11.5',
    ],
    'preact' => [
        'version' => '10.12.1',
    ],
    'preact/compat' => [
        'version' => '10.12.1',
    ],
    'preact/hooks' => [
        'version' => '10.12.1',
    ],
    '@fullcalendar/daygrid' => [
        'version' => '5.11.5',
    ],
    '@fullcalendar/common' => [
        'version' => '5.11.5',
    ],
    'tributejs' => [
        'version' => '5.1.3',
    ],
    'tributejs/dist/tribute.css' => [
        'version' => '5.1.3',
        'type' => 'css',
    ],
    'leaflet' => [
        'version' => '1.9.4',
    ],
    'leaflet/dist/leaflet.min.css' => [
        'version' => '1.9.4',
        'type' => 'css',
    ],
    'leaflet.markercluster' => [
        'version' => '1.5.3',
    ],
    'leaflet.markercluster/dist/MarkerCluster.min.css' => [
        'version' => '1.5.3',
        'type' => 'css',
    ],
    'leaflet-fullscreen' => [
        'version' => '1.0.2',
    ],
    'leaflet-fullscreen/dist/leaflet.fullscreen.css' => [
        'version' => '1.0.2',
        'type' => 'css',
    ],
    'tiny-markdown-editor' => [
        'version' => '0.1.23',
    ],
    'core-js/modules/es.regexp.flags.js' => [
        'version' => '3.38.1',
    ],
    'highcharts' => [
        'version' => '11.4.8',
    ],
    'highcharts/css/themes/dark-unica.css' => [
        'version' => '11.4.8',
        'type' => 'css',
    ],
    'leaflet.markercluster/dist/MarkerCluster.Default.css' => [
        'version' => '1.5.3',
        'type' => 'css',
    ],
    'leaflet.markercluster/dist/MarkerCluster.css' => [
        'version' => '1.5.3',
        'type' => 'css',
    ],
    'leaflet/dist/leaflet.css' => [
        'version' => '1.9.4',
        'type' => 'css',
    ],
    'slim-select' => [
        'version' => '2.9.0',
    ],
    'slim-select/dist/slimselect.min.css' => [
        'version' => '2.9.0',
        'type' => 'css',
    ],
    '@fullcalendar/core/index.js' => [
        'version' => '6.1.15',
    ],
    '@fullcalendar/interaction/index.js' => [
        'version' => '6.1.15',
    ],
    '@fullcalendar/daygrid/index.js' => [
        'version' => '6.1.15',
    ],
    '@fullcalendar/timegrid/index.js' => [
        'version' => '6.1.15',
    ],
    '@fullcalendar/list/index.js' => [
        'version' => '6.1.15',
    ],
    '@fullcalendar/multimonth/index.js' => [
        'version' => '6.1.15',
    ],
    '@fullcalendar/core/internal.js' => [
        'version' => '6.1.15',
    ],
    '@fullcalendar/core/preact.js' => [
        'version' => '6.1.15',
    ],
    'tslib' => [
        'version' => '2.7.0',
    ],
    '@fullcalendar/daygrid/internal.js' => [
        'version' => '6.1.15',
    ],
    '@symfony/ux-leaflet-map' => [
        'path' => './vendor/symfony/ux-leaflet-map/assets/dist/map_controller.js',
    ],
];
