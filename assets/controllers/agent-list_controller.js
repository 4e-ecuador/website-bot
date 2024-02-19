import { Controller } from '@hotwired/stimulus'

export default class extends Controller {
    static values = {
        url: String,
    }

    static targets = ['result', 'searchResultCount', 'pageCounter', 'btnPageUp', 'btnPageDown']

    q = ''
    page = 1

    connect() {
        this.refresh()
    }

    onSearchInput(event) {
        this.q = event.currentTarget.value
        this.page = 1
        this.refresh()
    }

    togglePage(event) {
        this.page += event.params.value
        this.refresh()
    }

    async refresh() {
        this.resultTarget.innerHTML = 'Refreshing...'
        const params = new URLSearchParams({
            q: this.q,
            page: this.page,
        })
        const response = await fetch(`${this.urlValue}?${params.toString()}`)

        const data = await response.json()

        this.searchResultCountTarget.innerText = data.msgSearchResultCount
        this.pageCounterTarget.innerText = data.msgPageCounter

        if (this.page === 1) {
            this.btnPageDownTarget.style.display = 'none'
        } else {
            this.btnPageDownTarget.style.display = 'block'
        }

        if (data.last === 0 || this.page === data.last) {
            this.btnPageUpTarget.style.display = 'none'
        } else {
            this.btnPageUpTarget.style.display = 'block'
        }

        this.resultTarget.innerHTML = data.list
    }
}
