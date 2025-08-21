import { Controller } from '@hotwired/stimulus'

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    map
    marker

    connect() {
        this.element.addEventListener('ux:map:marker:before-create', this._onMarkerBeforeCreate);
    }

    disconnect() {
        this.element.removeEventListener('ux:map:marker:before-create', this._onMarkerBeforeCreate);
    }

    _onMarkerBeforeCreate(event) {
        const { definition, L } = event.detail;

        const icon = L.icon({
            iconUrl: '/images/ico/my-icon.png',
            iconSize: [22, 36],
            iconAnchor: [11, 36],
        })

        definition.rawOptions = {
            icon: icon,
        }
    }
}
