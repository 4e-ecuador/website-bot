import { Controller } from '@hotwired/stimulus'
import { Modal } from 'bootstrap'

require('../../css/stats/leaderboard.css')

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static targets = ['modalBody']

    modal = null

    connect() {
        this.modal = new Modal('#detailsModal')
    }

    async showAll(e) {
        const old = e.target.innerHTML
        e.target.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span>'

        const formData = new FormData()
        formData.append('item', e.params.item)

        const response = await fetch(e.params.url, {
            method: 'POST',
            body: formData,
        })

        this.modalBodyTarget.innerHTML = await response.text()
        this.modal.show()

        e.target.innerHTML = old
    }
}
