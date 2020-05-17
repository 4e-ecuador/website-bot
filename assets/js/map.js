require('leaflet')
require('leaflet/dist/leaflet.css')
require('../css/map.css')

let map

function initmap(lat, lon) {
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

    map.setView(new L.LatLng(lat, lon), 12)

    L.marker([lat, lon], {icon: myIcon}).addTo(map)
}

let coords = document.querySelector('.js-agent-coords')
let lat = coords.dataset.lat
let lon = coords.dataset.lon

if (lat && lon) {
    initmap(lat, lon)
}
