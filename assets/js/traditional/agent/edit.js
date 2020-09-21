import $ from 'jquery'
import Map from '@/helper/Map'
import '../../../css/map/edit-map.css'

let lat = parseFloat($('#agent_lat').val().replace(',', '.'))
let lon = parseFloat($('#agent_lon').val().replace(',', '.'))
let zoom = 12

if (isNaN(lat)) {
    lat = -1.262326
    zoom = 5
}

if (isNaN(lon)) {
    lon = -79.09357
    zoom = 5
}

const map = new Map(lat, lon, zoom)

map.addDraggableMarker(lat, lon, function () {
    const latLng = this.getLatLng()
    console.log(latLng.lat)
    $('#agent_lat').val(latLng.lat)
    $('#agent_lon').val(latLng.lng)
})
