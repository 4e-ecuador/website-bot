import { Controller } from 'stimulus'

export default class extends Controller {
    static values = {
        url: String,
    }

    static targets = ['result']

    async updateView(event){
        const params = new URLSearchParams({
            agents: JSON.stringify(event.detail.ids),
        })

        this.resultTarget.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span>'

        const response = await fetch(`${this.urlValue}?${params.toString()}`)
        this.resultTarget.innerHTML = await response.text()
    }
}
