import { Controller } from '@hotwired/stimulus'

import Map from '../../js/helper/Map.js'

import '../../css/map/edit-map.css'

const TinyMDE = require('tiny-markdown-editor')
import '../../styles/tiny-mde.css'

import Tribute from 'tributejs'
import 'tributejs/dist/tribute.css'
import '../../css/helper/tribute.css'

import { Modal } from 'bootstrap'

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static values = {
        agentId: Number,
        lat: Number,
        lon: Number,
        commentLookupUrl: String,
        commentsLookupUrl: String,
        previewUrl: String,
        tributeLookupUrl: String,
    }

    static targets = [
        'commentArea', 'commentStatus', 'commentMessage', 'commentForm', 'commentText', 'preview'
    ]

    editor = null

    connect() {
        const map = new Map(this.latValue, this.lonValue, 15)
        map.addMarker(this.latValue, this.lonValue)

        if (this.agentIdValue) {
            this.getComments()
        }

        this.commentFormTarget.addEventListener('submit', this.handleFormSubmit.bind(this))

        this.editor = new TinyMDE.Editor({ element: this.commentFormTarget.querySelector('#comment_text') })
        const commandBar1 = new TinyMDE.CommandBar({
            element: 'tinymde_commandbar',
            editor: this.editor,
            commands: [
                {
                    name: 'preview',
                    title: 'Show a preview',
                    innerHTML: '<span class="oi oi-eye"></span>',
                    action: editor => this.preview(editor)
                },
                'bold', 'italic', 'strikethrough', '|', 'code', '|', 'h1', 'h2', '|', 'ul', 'ol',
                '|', 'blockquote', 'hr', '|', 'insertLink', 'insertImage']
        })

        const tribute = new Tribute({
            values: function (text, cb) {
                this._tributeSearch(text, users => cb(users))
            }.bind(this),
            lookup: 'name',
            fillAttr: 'name',
            menuItemTemplate: function (item) {
                return '<img src="/build/images/logos/' + item.original.faction + '.svg" style="width: 24px"> ' + item.string
            }
        })

        tribute.attach(document.querySelectorAll('.TinyMDE'))
    }

    async _tributeSearch(text, cb) {
        const formData = new FormData()
        formData.append('query', text)

        const response = await fetch(this.tributeLookupUrlValue, {
            method: 'POST',
            body: formData,
        })

        const data = await response.json()

        if (data && data.length > 0) {
            cb(data)
        }else{
            cb([])
        }
    }

    async getComments() {
        this.commentStatusTarget.innerText = 'Fetching comments... '
        this.commentAreaTarget.innerText = ''

        const formData = new FormData()
        formData.append('agent_id', this.agentIdValue)

        const response = await fetch(this.commentsLookupUrlValue, {
            method: 'POST',
            body: formData,
        })

        const data = await response.json()

        this.commentAreaTarget.innerHTML = data.comments
        this.commentStatusTarget.innerText = ''
    }

    async getComment(id) {
        this.commentStatusTarget.innerText = 'Fetching comment... '

        const formData = new FormData()
        formData.append('comment_id', id)

        const response = await fetch(this.commentLookupUrlValue, {
            method: 'POST',
            body: formData,
        })

        const data = await response.json()

        this.commentAreaTarget.innerHTML += data.comment
        this.commentStatusTarget.innerHTML = ''
    }

    async handleFormSubmit(e) {
        e.preventDefault()
        this.commentStatusTarget.innerText = 'Submitting comment... '

        const response = await fetch(e.target.action, {
            method: 'POST',
            body: new FormData(e.target),
        })

        const data = await response.json()

        if (data.error) {
            this.commentMessageTarget.innerText = data.error
        } else {
            this.commentMessageTarget.innerText = 'Your comment has been submitted.'
            await this.getComment(data.id)
        }

        this.commentStatusTarget.innerText = ''
        this.editor.setContent('')
    }

    async preview(editor) {
        const formData = new FormData()
        formData.append('text', editor.getContent())

        const response = await fetch(this.previewUrlValue, {
            method: 'POST',
            body: formData,
        })

        const data = await response.json()
        this.previewTarget.innerHTML = data.data
        const modal = new Modal('#previewModal')
        modal.show()
        modal.handleUpdate()
    }
}
