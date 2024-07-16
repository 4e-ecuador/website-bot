import { Controller } from '@hotwired/stimulus';
import Map from '../js/helper/Map.js'

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static values = {
        defaultLat: Number,
        defaultLon: Number,
        mapGroups: Array,
    }

    map=null

    connect() {
        this.map = new Map(this.defaultLatValue, this.defaultLonValue)

        this.map.addLegend(this.mapGroupsValue)
        this.map.loadMarkers(this.mapGroupsValue[0])
    }

    changeGroup(e) {
        this.map.loadMarkers(e.target.value)

    }
}
