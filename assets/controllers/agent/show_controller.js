import { Controller } from '@hotwired/stimulus'

import Map from '../../js/helper/Map.js'

import '../../css/map/edit-map.css'

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static values = {
        agentId: Number,
        lat: Number,
        lon: Number,
        commentLookupUrl: String,
        commentsLookupUrl: String,
        agentLookupUrl: String,
        previewUrl: String,
    }

    static targets = [
        'commentArea', 'commentStatus', 'commentMessage', 'commentForm', 'commentText', 'preview'
    ]

    connect() {
        const map = new Map(this.latValue, this.lonValue, 15)
        map.addMarker(this.latValue, this.lonValue)

        if (this.agentIdValue) {
            this.getComments()
        }

        this.commentFormTarget.addEventListener('submit', this.handleFormSubmit.bind(this))
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
            this.commentMessageTarget.innerText = ''
            await this.getComment(data.id)
        }

        this.commentStatusTarget.innerText = ''
        this.commentTextTarget.innerText = ''
    }

    async preview() {
        this.previewTarget.innerText = 'Loading...'

        const formData = new FormData()
        formData.append('text', this.commentTextTarget.value)

        const response = await fetch(this.previewUrlValue, {
            method: 'POST',
            body: formData,
        })

        const data = await response.json()
        this.previewTarget.innerHTML = data.data
    }
}
