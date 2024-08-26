import { Controller } from '@hotwired/stimulus'

import Map from '../js/helper/Map.js'

import '../css/map/edit-map.css'
import '../css/account.css'

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static targets = ['lat', 'lon']

    connect() {
        let lat = parseFloat(this.latTarget.value.replace(',', '.'))
        let lon = parseFloat(this.lonTarget.value.replace(',', '.'))
        let zoom = 12

        if (isNaN(lat) || isNaN(lon)) {
            lat = -1.262326
            lon = -79.09357
            zoom = 5
        }

        const map = new Map(lat, lon, zoom)

        map.addDraggableMarker(lat, lon, function (e) {
            this.latTarget.value = e.latlng.lat
            this.lonTarget.value = e.latlng.lng
        }.bind(this))
    }

    updateMedals(e) {
        let element

        if ('SPAN' === e.target.tagName) {
            element = e.target.parentElement
        } else {
            element = e.target
        }

        let input = document.getElementById(element.dataset.for)

        if (input.checked) {
            input.checked = false
            element.classList.remove('medalSelected')
        } else {
            Array.prototype.forEach.call(document.getElementsByName(input.name), function (el) {
                document.querySelector('label[data-for=' + el.id + ']')
                    .classList
                    .remove('medalSelected')
            })

            input.checked = true
            element.classList.add('medalSelected')
        }
    }
}
