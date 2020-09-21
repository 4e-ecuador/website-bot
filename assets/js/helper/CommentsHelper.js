import $ from 'jquery'

import Tribute from 'tributejs'
import 'tributejs/dist/tribute.css'
import '../../css/helper/tribute.css'

class CommentsHelper {
    constructor($commentArea, $commentMessage, $commentStatus, $form, tributeLookupUrl) {
        this.commentArea = $commentArea
        this.commentMessage = $commentMessage
        this.commentStatus = $commentStatus
        this.tributeLookupUrl = tributeLookupUrl
        this.form = $form

        this.form.submit(this._handleFormSubmit.bind(this))


        const self = this

        const tribute = new Tribute({
            values: function (text, cb) {
                self._tributeSearch(text, users => cb(users))
            },
            lookup: 'name',
            fillAttr: 'name',
            menuItemTemplate: function (item) {
                return '<img src="/build/images/logos/' + item.original.faction + '.svg" style="width: 24px"> ' + item.string
            }
        })

        tribute.attach(document.querySelectorAll('.mentionable'))
    }

    getComments(agentId) {
        this.commentStatus.html('Fetching comments... ')
        this.commentArea.html('')

        const self = this
        $.ajax({
            type: 'POST',
            url: '/comment/getagentids',
            data: { agent_id: agentId }
        })
            .done(function (r) {
                self.commentArea.html(r.comments)

                self.commentStatus.html('')
            })
    }

    _getComment(id) {
        this.commentStatus.html('Fetching comment... ')

        const self = this
        $.ajax({
            method: 'POST',
            url: '/comment/fetch',
            data: { comment_id: id }
        })
            .done(function (r) {
                self.commentArea.html(self.commentArea.html() + r.comment)

                self.commentStatus.html('')
            })
    }

    _clearForm() {
        $('#comment_text').val('')
    }

    _handleFormSubmit(e) {
        this.commentStatus.html('Submitting comment... ')

        e.preventDefault()

        const self = this

        $.ajax({
            type: self.form.attr('method'),
            url: self.form.attr('action'),
            data: self.form.serialize(),
            success: function (data) {
                if (data.error) {
                    self.commentMessage.html(data.error)
                } else {
                    self.commentMessage.html()
                    self._getComment(data.id)
                }

                self.commentStatus.html()
                self._clearForm()
            },
            error: function (data) {
                console.log('An error occurred.')
                console.log(data)
                self.commentStatus.html('An error occurred.')
            },
        })
    }

    _tributeSearch(text, cb) {
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
        xhr.open('POST', this.tributeLookupUrl + '?query=' + text, true)
        xhr.send()
    }
}

export default CommentsHelper
