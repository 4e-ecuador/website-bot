const $ = require('jquery')

require('leaflet')
require('leaflet/dist/leaflet.css')
require('../css/map-edit.css')

let map

function initmap(lat, lon, zoom) {
    const osmUrl = 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png'
    const osmAttrib = 'Map data (C) <a href="https://openstreetmap.org">OpenStreetMap</a> contributors'
    const osm = new L.TileLayer(osmUrl, {attribution: osmAttrib})
    const myIcon = L.icon({
        iconUrl: '/build/images/ico/my-icon.png',
        iconSize: [22, 36],
        iconAnchor: [11, 36],
        popupAnchor: [0, -18],
    })

    map = new L.Map('map')

    map.addLayer(osm)

    map.setView(new L.LatLng(lat, lon), zoom)

    let marker = L.marker([lat, lon], {
        draggable: 'true',
        icon: myIcon
    }).addTo(map)

    marker.on('drag', function () {
        const latlng = marker.getLatLng()
        $('#agent_lat').val(latlng.lat)
        $('#agent_lon').val(latlng.lng)
    })
}

let lat = parseFloat($('#agent_lat').val().replace(',', '.'))
let lon = parseFloat($('#agent_lon').val().replace(',', '.'))
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
