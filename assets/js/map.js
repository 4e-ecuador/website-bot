require('leaflet')
require('leaflet/dist/leaflet.css')
require('../css/map.css')

// Leaflet icon hack start
import L from 'leaflet';
delete L.Icon.Default.prototype._getIconUrl;

L.Icon.Default.mergeOptions({
    iconRetinaUrl: require('leaflet/dist/images/marker-icon-2x.png'),
    iconUrl: require('leaflet/dist/images/marker-icon.png'),
    shadowUrl: require('leaflet/dist/images/marker-shadow.png'),
});
// Leaflet icon hack end

let map

function initmap(lat, lon) {
    var osmUrl = 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png'
    var osmAttrib = 'Map data (C) <a href="https://openstreetmap.org">OpenStreetMap</a> contributors'
    var osm = new L.TileLayer(osmUrl, {attribution: osmAttrib})

    map = new L.Map('map')

    map.addLayer(osm)

    map.setView(new L.LatLng(lat, lon), 12)

    L.marker([lat, lon]).addTo(map);
}

let coords = document.querySelector('.js-agent-coords')
let lat = coords.dataset.lat
let lon = coords.dataset.lon

if (lat && lon) {
    initmap(lat, lon)
}
