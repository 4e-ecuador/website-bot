const $ = require('jquery')

require('leaflet')
require('leaflet/dist/leaflet.css')

require('leaflet-fullscreen')
require('leaflet-fullscreen/dist/leaflet.fullscreen.css')

require('../css/account.css')

let map

function initmap(lat, lon, zoom) {
    const osmUrl = 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png'
    const osmAttrib = 'Map data (C) <a href="https://openstreetmap.org">OpenStreetMap</a> contributors'
    const osm = new L.TileLayer(osmUrl, {attribution: osmAttrib})

    map = new L.Map('map', {fullscreenControl: true})

    map.addLayer(osm)

    map.setView(new L.LatLng(lat, lon), zoom)

    const myIcon = L.icon({
        iconUrl: '/build/images/ico/my-icon.png',
        iconSize: [22, 36],
        iconAnchor: [11, 36],
        popupAnchor: [0, -18],
    })

    let marker = L.marker([lat, lon], {
        draggable: 'true', icon: myIcon
    }).addTo(map)

    marker.on('drag', function () {
        const latlng = marker.getLatLng()
        $('#agent_account_lat').val(latlng.lat)
        $('#agent_account_lon').val(latlng.lng)
    })
}

let lat = parseFloat($('#agent_account_lat').val().replace(',', '.'))
let lon = parseFloat($('#agent_account_lon').val().replace(',', '.'))
let zoom = 12

if (isNaN(lat)) {
    lat = -1.262326
    zoom = 5
}
if (isNaN(lon)) {
    lon = -79.09357
    zoom = 5
}

initmap(lat, lon, zoom)

$('.medalLabel')
    .each(function () {
        let input = $('#' + $(this).data('for'))

        if (input.prop('checked')) {
            $(this).addClass('medalSelected')
        }
    })
    .on('click', function () {
        let input = $('#' + $(this).data('for'))

        if (input.prop('checked')) {
            input.prop('checked', false)
            $(this).removeClass('medalSelected')
        } else {
            input.prop('checked', true)
            $(this).addClass('medalSelected')
        }
    })

$('.medalsLabel')
    .each(function () {
        let input = $('#' + $(this).data('for'))

        if (input.prop('checked')) {
            $(this).addClass('medalSelected')
        }
    })
    .on('click', function () {
        let input = $('#' + $(this).data('for'))

        if (input.prop('checked')) {
            input.prop('checked', false)
            $(this).removeClass('medalSelected')
        } else {
            input.prop('checked', true)
            $('input[name^=\'' + input.prop('name') + '\']').each(function () {
                $('label[data-for=' + $(this).prop('id') + ']').removeClass('medalSelected')
            })
            $(this).addClass('medalSelected')
        }
    })
