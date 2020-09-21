import $ from 'jquery'
import Map from '@/helper/Map'

import '../../../css/map/edit-map.css'
import  '../../../css/traditional/account/index.css'

let lat = parseFloat($('#agent_account_lat').val().replace(',', '.'))
let lon = parseFloat($('#agent_account_lon').val().replace(',', '.'))
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
    $('#agent_account_lat').val(latLng.lat)
    $('#agent_account_lon').val(latLng.lng)
})

$('.medalLabel')
    .each(function () {
        let input = $('#' + $(this).data('for'))

        if (input.prop('checked')) {
            $(this).addClass('medalSelected')
        }
    })
    .on('click', function () {
        let input = $('#' + $(this).data('for'))

        if (input.prop('checked')) {
            input.prop('checked', false)
            $(this).removeClass('medalSelected')
        } else {
            input.prop('checked', true)
            $(this).addClass('medalSelected')
        }
    })

$('.medalsLabel')
    .each(function () {
        let input = $('#' + $(this).data('for'))

        if (input.prop('checked')) {
            $(this).addClass('medalSelected')
        }
    })
    .on('click', function () {
        let input = $('#' + $(this).data('for'))

        if (input.prop('checked')) {
            input.prop('checked', false)
            $(this).removeClass('medalSelected')
        } else {
            input.prop('checked', true)
            $('input[name^=\'' + input.prop('name') + '\']').each(function () {
                $('label[data-for=' + $(this).prop('id') + ']').removeClass('medalSelected')
            })
            $(this).addClass('medalSelected')
        }
    })
