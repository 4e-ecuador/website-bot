const $ = require('jquery')

require('leaflet')
require('leaflet/dist/leaflet.css')
require('../css/map-edit.css')

// Leaflet icon hack start
import L from 'leaflet'

delete L.Icon.Default.prototype._getIconUrl

L.Icon.Default.mergeOptions({
    iconRetinaUrl: require('leaflet/dist/images/marker-icon-2x.png'),
    iconUrl: require('leaflet/dist/images/marker-icon.png'),
    shadowUrl: require('leaflet/dist/images/marker-shadow.png'),
})
// Leaflet icon hack end

let map

function initmap(lat, lon, zoom) {
    var osmUrl = 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png'
    var osmAttrib = 'Map data (C) <a href="https://openstreetmap.org">OpenStreetMap</a> contributors'
    var osm = new L.TileLayer(osmUrl, {attribution: osmAttrib})

    map = new L.Map('map')

    map.addLayer(osm)

    map.setView(new L.LatLng(lat, lon), zoom)

    let marker = L.marker([lat, lon], {
        draggable: 'true'
    }).addTo(map)

    marker.on('drag', function () {
        var latlng = marker.getLatLng()
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
