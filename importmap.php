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
 *
 * @return array<string, array{    // Import name as key, description of the imported file as value
 *     path: string,               // Logical, relative or absolute path to the file
 *     type?: 'js'|'css'|'json',   // Type of the file, defaults to 'js'
 *     entrypoint?: bool,          // Whether the file is an entrypoint, for 'js' only
 * }|array{
 *     version: string,            // Version of the remote package
 *     package_specifier?: string, // Remote "package-name/path" specifier, defaults to the import name
 *     type?: 'js'|'css'|'json',
 *     entrypoint?: bool,
 * }>
 */
return [
    'app' => ['path' => './assets/app.js', 'entrypoint' => true],
    '@symfony/stimulus-bundle' => ['path' => './vendor/symfony/stimulus-bundle/assets/dist/loader.js'],
    '@symfony/ux-leaflet-map' => ['path' => './vendor/symfony/ux-leaflet-map/assets/dist/map_controller.js'],
    'fullcalendar' => ['path' => './assets/vendor_local/fullcalendar/index.js'],
    '@fullcalendar/core' => ['path' => './assets/vendor_local/fullcalendar-core/index.js'],
    'preact' => ['path' => './assets/vendor_local/preact/preact.js'],
    'preact/compat' => ['path' => './assets/vendor_local/preact/compat.js'],
    'preact/hooks' => ['path' => './assets/vendor_local/preact/hooks.js'],
    '@fullcalendar/daygrid' => ['path' => './assets/vendor_local/fullcalendar-daygrid/index.js'],
    '@fullcalendar/core/index.js' => ['path' => './assets/vendor_local/fullcalendar-core/index.js'],
    '@fullcalendar/interaction/index.js' => ['path' => './assets/vendor_local/fullcalendar-interaction/index.js'],
    '@fullcalendar/daygrid/index.js' => ['path' => './assets/vendor_local/fullcalendar-daygrid/index.js'],
    '@fullcalendar/timegrid/index.js' => ['path' => './assets/vendor_local/fullcalendar-timegrid/index.js'],
    '@fullcalendar/list/index.js' => ['path' => './assets/vendor_local/fullcalendar-list/index.js'],
    '@fullcalendar/multimonth/index.js' => ['path' => './assets/vendor_local/fullcalendar-multimonth/index.js'],
    '@fullcalendar/core/internal.js' => ['path' => './assets/vendor_local/fullcalendar-core/internal.js'],
    '@fullcalendar/core/preact.js' => ['path' => './assets/vendor_local/fullcalendar-core/preact.js'],
    '@fullcalendar/daygrid/internal.js' => ['path' => './assets/vendor_local/fullcalendar-daygrid/internal.js'],
    '@hotwired/stimulus' => ['version' => '3.2.2'],
    'bootstrap' => ['version' => '5.3.8'],
    '@popperjs/core' => ['version' => '2.11.8'],
    'bootstrap/dist/css/bootstrap.min.css' => ['version' => '5.3.8', 'type' => 'css'],
    'stimulus-use' => ['version' => '0.52.3'],
    'js-datepicker' => ['version' => '5.18.4'],
    'js-datepicker/dist/datepicker.min.css' => ['version' => '5.18.4', 'type' => 'css'],
    'open-iconic/font/css/open-iconic-bootstrap.css' => ['version' => '1.1.1', 'type' => 'css'],
    'tributejs' => ['version' => '5.1.3'],
    'tributejs/dist/tribute.css' => ['version' => '5.1.3', 'type' => 'css'],
    'leaflet' => ['version' => '1.9.4'],
    'leaflet/dist/leaflet.min.css' => ['version' => '1.9.4', 'type' => 'css'],
    'leaflet.markercluster' => ['version' => '1.5.3'],
    'leaflet.markercluster/dist/MarkerCluster.min.css' => ['version' => '1.5.3', 'type' => 'css'],
    'leaflet-fullscreen' => ['version' => '1.0.2'],
    'leaflet-fullscreen/dist/leaflet.fullscreen.css' => ['version' => '1.0.2', 'type' => 'css'],
    'tiny-markdown-editor' => ['version' => '0.2.19'],
    'core-js/modules/es.regexp.flags.js' => ['version' => '3.48.0'],
    'highcharts' => ['version' => '12.5.0'],
    'highcharts/css/themes/dark-unica.css' => ['version' => '12.5.0', 'type' => 'css'],
    'leaflet.markercluster/dist/MarkerCluster.Default.css' => ['version' => '1.5.3', 'type' => 'css'],
    'leaflet.markercluster/dist/MarkerCluster.css' => ['version' => '1.5.3', 'type' => 'css'],
    'leaflet/dist/leaflet.css' => ['version' => '1.9.4', 'type' => 'css'],
    'slim-select' => ['version' => '3.4.1'],
    'slim-select/dist/slimselect.min.css' => ['version' => '3.4.1', 'type' => 'css'],
    'tslib' => ['version' => '2.8.1'],
];
