const $ = require('jquery')

require('leaflet')
require('leaflet/dist/leaflet.css')

require('leaflet.markercluster')
require('leaflet.markercluster/dist/MarkerCluster.css')
require('leaflet.markercluster/dist/MarkerCluster.Default.css')

require('../css/agents-map.css')

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

function initmap(lat, lon) {
    const osmUrl = 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png'
    const osmAttrib = 'Map data (C) <a href="https://openstreetmap.org">OpenStreetMap</a> contributors'
    const osm = new L.TileLayer(osmUrl, {attribution: osmAttrib})

    map = new L.Map('map')

    map.addLayer(osm)

    map.setView(new L.LatLng(lat, lon), 7)
}

const markers = L.markerClusterGroup({disableClusteringAtZoom: 16})

function loadMarkers() {
    markers.clearLayers()

    $.get('/map_json', {}, function (data) {
        $(data).each(function () {
            let marker =
                new L.Marker(
                    new L.LatLng(this.lat, this.lng),
                    {
                        wp_id: this.id, wp_selected: false//, title: this.name
                    }
                )

            marker.bindPopup('Loading...')

            marker.on('click', function (e) {
                let popup = e.target.getPopup()
                $.get('/map/agent-info/' + e.target.options.wp_id).done(function (data) {
                    popup.setContent(data)
                    popup.update()
                })
            })

            markers.addLayer(marker)
            map.addLayer(markers)
        })
    }, 'json')
}

let lat = -1.262326
let lon = -79.09357

initmap(lat, lon)
loadMarkers()
