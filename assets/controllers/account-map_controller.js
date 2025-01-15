import { Controller } from '@hotwired/stimulus'

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    map
    marker

    connect() {
        this.element.addEventListener('ux:map:connect', this._onConnect.bind(this))
    }

    disconnect() {
        this.element.removeEventListener('ux:map:connect', this._onConnect)
    }

    _onConnect(event) {
        this.map = event.detail.map

        this.map.on('move', this._updateMarker.bind(this))
        this.map.on('moveend', this._updateControls.bind(this))

        const icon = L.icon({
            iconUrl: '/images/ico/my-icon.png',
            iconSize: [22, 36],
            iconAnchor: [11, 36],
            popupAnchor: [0, -18],
        })

        this.marker = L.marker(this.map.getCenter(), { icon: icon })
            .addTo(this.map)
    }

    _updateMarker() {
        this.marker.setLatLng(this.map.getCenter())
    }

    _updateControls() {
        this.dispatch('update', { detail: { center: this.map.getCenter() } })
    }
}
