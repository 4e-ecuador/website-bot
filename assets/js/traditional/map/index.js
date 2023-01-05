import $ from 'jquery'
import Map from '@/helper/Map'

import '../../../css/traditional/map/index.css'

const mapData = document.getElementById('map').dataset

const map = new Map(mapData.lat, mapData.lon)

map.addLegend(JSON.parse(mapData.groups))
map.loadMarkers($('#groupSelect').val())
