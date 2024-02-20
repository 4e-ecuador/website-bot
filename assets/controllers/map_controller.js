import { Controller } from '@hotwired/stimulus';
import Map from '@/helper/Map'

import '../css/map/main.css'

export default class extends Controller {
    static values = {
        defaultLat: Number,
        defaultLon: Number,
        mapGroups: Array,
    }

    connect() {
        const map = new Map(this.defaultLatValue, this.defaultLonValue)

        map.addLegend(this.mapGroupsValue)
        map.loadMarkers(this.mapGroupsValue[0])
    }
}
