import $ from 'jquery'
import Map from '@/helper/Map'
import '../../../css/map/edit-map.css'

import CommentsHelper from '@/helper/CommentsHelper'

const coords = $('.js-agent-coords')
const lat = coords.data('lat')
const lon = coords.data('lon')

if (lat && lon) {
    const map = new Map(lat, lon, 15)
    map.addMarker(lat, lon)
}

const commentsHelper = new CommentsHelper(
    $('#commentArea'), $('#commentMessage'), $('#commentStatus'), $('#commentForm'),
    $('#js-data')
        .data('lookup-url')
)

const agentId = $('#js-agent-id')
    .data('js-agent-id')

if (agentId) {
    commentsHelper.getComments(agentId)
}
