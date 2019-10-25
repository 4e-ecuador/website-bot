const $ = require('jquery')

const commentArea = $('#commentArea')
const commentMessage = $('#commentMessage')
const commentStatus = $('#commentStatus')

const frm = $('#commentForm')

const lookupUrl = $('#js-data').data('lookup-url')

import Tribute from 'tributejs'
import 'tributejs/dist/tribute.css'

require('../css/editor.css')

const tribute = new Tribute({
    values: function (text, cb) {
        remoteSearch(text, users => cb(users))
    },
    lookup: 'name',
    fillAttr: 'name'
})

tribute.attach(document.querySelectorAll('.mentionable'))

function getComments(agentId) {
    commentStatus.html('Fetching comments... ')
    commentArea.html('')

    $.ajax({
        type: 'POST',
        url: '/comment/getagentids',
        data: {agent_id: agentId}
    })
        .done(function (r) {
            commentArea.html(r.comments)

            commentStatus.html('')
        })
}

function getComment(id) {
    commentStatus.html('Fetching comment... ')

    $.ajax({
        method: 'POST',
        url: '/comment/fetch',
        data: {comment_id: id}
    })
        .done(function (r) {
            commentArea.html(commentArea.html() + r.comment)

            commentStatus.html('')
        })
}

function clearForm() {
    $('#comment_text').val('')
}

frm.submit(function (e) {
    commentStatus.html('Submitting comment... ')

    e.preventDefault()

    $.ajax({
        type: frm.attr('method'),
        url: frm.attr('action'),
        data: frm.serialize(),
        success: function (data) {
            if (data.error) {
                commentMessage.html(data.error)
            } else {
                commentMessage.html()
                getComment(data.id)
            }

            commentStatus.html()
            clearForm()
        },
        error: function (data) {
            console.log('An error occurred.')
            console.log(data)
            commentStatus.html('An error occurred.')
        },
    })
})

let agentId = $('#js-agent-id').data('js-agent-id')

if (agentId) {
    getComments(agentId)
}

function remoteSearch(text, cb) {
    let xhr = new XMLHttpRequest()
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4) {
            if (xhr.status === 200) {
                const data = JSON.parse(xhr.responseText)
                cb(data)
            } else if (xhr.status === 403) {
                cb([])
            }
        }
    }
    xhr.open('POST', lookupUrl + '?query=' + text, true)
    xhr.send()
}
