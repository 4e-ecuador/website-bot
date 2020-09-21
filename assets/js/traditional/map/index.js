import $ from 'jquery'
import Map from '@/helper/Map'

import '../../../css/traditional/map/index.css'

let lat = -1.262326
let lon = -79.09357

const map = new Map(lat, lon)

map.addLegend($('#jsData').data('mapgroups'))
map.loadMarkers($('#groupSelect').val())
