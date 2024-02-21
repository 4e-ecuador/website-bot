import { Controller } from '@hotwired/stimulus'
import Map from '../js/helper/Map.js'

import '../css/map/edit-map.css'

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static targets = ['lat', 'lon']
    static values = {
        defaultLat: Number,
        defaultLon: Number,
    }

    connect() {
        let lat = this.latTarget.value
        let lon = this.lonTarget.value

        let zoom = 12

        if (isNaN(lat) || isNaN(lon)) {
            lat = this.defaultLatValue
            lon = this.defaultLonValue
            zoom = 5
        }

        const map = new Map(lat, lon, zoom)

        map.addDraggableMarker(lat, lon, this.onDrag.bind(this))
    }

    onDrag(e) {
        this.latTarget.value = e.latlng.lat
        this.lonTarget.value = e.latlng.lng
    }
}
