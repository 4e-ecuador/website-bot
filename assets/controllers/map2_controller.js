import { Controller } from '@hotwired/stimulus'
import L from 'leaflet'

import 'leaflet-fullscreen'
import 'leaflet-fullscreen/dist/leaflet.fullscreen.css'

import 'leaflet.markercluster'
import 'leaflet.markercluster/dist/MarkerCluster.css'
import 'leaflet.markercluster/dist/MarkerCluster.Default.css'

import '../css/map/main.css'

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    map = null
    mapGroups = []

    connect() {
        this.element.addEventListener('ux:map:pre-connect', this._onPreConnect.bind(this))
        this.element.addEventListener('ux:map:connect', this._onConnect.bind(this))

        this.icon = L.icon({
            iconUrl: '/images/ico/my-icon.png',
            iconSize: [22, 36],
            iconAnchor: [11, 36],
            popupAnchor: [0, -18],
        })
    }

    disconnect() {
        this.element.removeEventListener('ux:map:pre-connect', this._onPreConnect)
        this.element.removeEventListener('ux:map:connect', this._onConnect)
    }

    _onPreConnect(event) {
        this.mapGroups = event.detail.extra.mapGroups
        console.log(this.mapGroups)

        event.detail.bridgeOptions = {
            fullscreenControl: true
        }
    }

    _onConnect(event) {
        this.map = event.detail.map
        this.markers = L.markerClusterGroup({ disableClusteringAtZoom: 16 })

        // Force recalculation after CSS is applied
        setTimeout(() => this.map.invalidateSize(), 100)

        this.loadMarkers(this.mapGroups[0])
    }

    async loadMarkers(group) {
        this.markers.clearLayers()

        const response = await fetch('map_json?group=' + group)
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
                const response = await fetch('/map/agent-info/' + e.target.options.wp_id)
                const data = await response.text()
                popup.setContent(data)
                popup.update()
            })

            this.markers.addLayer(marker)
        }

        this.map.addLayer(this.markers)
    }

}
