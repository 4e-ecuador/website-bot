import 'leaflet'
import 'leaflet/dist/leaflet.css'

import 'leaflet.markercluster'
import 'leaflet.markercluster/dist/MarkerCluster.css'
import 'leaflet.markercluster/dist/MarkerCluster.Default.css'

import 'leaflet-fullscreen'
import 'leaflet-fullscreen/dist/leaflet.fullscreen.css'

class Map {
    constructor(centerLat, centerLon, zoom = 7) {
        this.map = new L.Map('map', { fullscreenControl: true })
        this.map.setView(new L.LatLng(centerLat, centerLon), zoom)
        this.markers = L.markerClusterGroup({ disableClusteringAtZoom: 16 })

        this.icon = L.icon({
            iconUrl: '/build/images/ico/my-icon.png',
            iconSize: [22, 36],
            iconAnchor: [11, 36],
            popupAnchor: [0, -18],
        })

        this._initMap()
    }

    addMarker(lat, lon) {
        L.marker([lat, lon], { icon: this.icon })
            .addTo(this.map)
    }

    addDraggableMarker(lat, lon, onDrag) {
        let marker = L.marker([lat, lon], {
            draggable: 'true',
            icon: this.icon
        })
            .addTo(this.map)
        marker.on('drag', onDrag)
    }

    async loadMarkers(group) {
        this.markers.clearLayers()

        const response = await fetch('map_json?group='+group)
        const data = await response.json()

        for (const point of data) {
            let marker =
                new L.Marker(
                    new L.LatLng(point.lat, point.lng),
                    {
                        wp_id: point.id,
                        icon: this.icon
                    }
                )

            marker.bindPopup('Loading...')

            marker.on('click', async function (e) {
                let popup = e.target.getPopup()
                const response = await fetch('/map/agent-info/'+e.target.options.wp_id)
                const data = await response.text()
                popup.setContent(data)
                popup.update()
            })

            this.markers.addLayer(marker)
        }

        this.map.addLayer(this.markers)
    }

    addLegend(groups) {
        let legend = L.control({ position: 'topleft' })
        legend.onAdd = function () {
            let div = L.DomUtil.create('div', 'info legend')
            // @todo Bootstrap selectpicker does not work :(
            div.innerHTML =
                '<a class="btn btn-sm btn-outline-secondary" href="/">Home</a><br>'
                + '<select data-action="map#changeGroup" class="selectpickerXXX" data-style="btn-success" data-width="fit">'
                + '<option>' + groups.join('</option><option>') + '</option>'
                + '</select>'
            div.firstChild.onmousedown = div.firstChild.ondblclick = L.DomEvent.stopPropagation
            L.DomEvent.disableClickPropagation(div)
            return div
        }

        legend.addTo(this.map)
    }

    _initMap() {
        const osmUrl = 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png'
        const osmAttrib = 'Map data (C) <a href="https://openstreetmap.org">OpenStreetMap</a> contributors'
        const osm = new L.TileLayer(osmUrl, { attribution: osmAttrib })

        this.map.addLayer(osm)

        L.Control.Watermark = L.Control.extend({
            onAdd: function () {
                let img = L.DomUtil.create('img')
                img.src = '/build/images/logos/4E Global black RGB.png'
                img.style.width = '100px'

                return img
            },
        })

        L.control.watermark = function (opts) {
            return new L.Control.Watermark(opts)
        }

        L.control.watermark({ position: 'bottomleft' })
            .addTo(this.map)
    }
}

export default Map

