import $ from 'jquery'

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

    loadMarkers(group) {
        this.markers.clearLayers()

        const icon = this.icon
        const markers = this.markers
        const map = this.map

        $.get('/map_json', { 'group': group }, function (data) {
            $(data)
                .each(function () {
                    let marker =
                        new L.Marker(
                            new L.LatLng(this.lat, this.lng),
                            {
                                wp_id: this.id,
                                icon: icon
                            }
                        )

                    marker.bindPopup('Loading...')

                    marker.on('click', function (e) {
                        let popup = e.target.getPopup()
                        $.get('/map/agent-info/' + e.target.options.wp_id)
                            .done(function (data) {
                                popup.setContent(data)
                                popup.update()
                            })
                    })

                    markers.addLayer(marker)
                    map.addLayer(markers)
                })
        }, 'json')
    }

    addLegend(groups) {
        let legend = L.control({ position: 'topleft' })
        legend.onAdd = function () {
            let div = L.DomUtil.create('div', 'info legend')
            div.innerHTML =
                '<a class="btn btn-sm btn-outline-secondary" href="/">Home</a><br>'
                + '<select id="groupSelect" class="selectpicker" data-style="btn-success" data-width="fit">'
                + '<option>' + groups.join('</option><option>') + '</option>'
                + '</select>'
            div.firstChild.onmousedown = div.firstChild.ondblclick = L.DomEvent.stopPropagation
            L.DomEvent.disableClickPropagation(div)
            return div
        }

        legend.addTo(this.map)

        const self = this
        $('#groupSelect')
            .on('change', function () {
                self.loadMarkers($(this)
                    .val())
            })
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

