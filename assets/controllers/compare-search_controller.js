import { Controller } from 'stimulus'
import { useClickOutside, useDebounce, useDispatch } from 'stimulus-use'

export default class extends Controller {
    static values = {
        urlSearch: String,
        urlAgentList: String,
    }

    static targets = ['input', 'resultItem', 'result', 'agentList']

    static debounces = ['_search']

    agentsIds = []
    currentSelection = 0

    connect() {
        useClickOutside(this)
        useDebounce(this)
        useDispatch(this);
        this.inputTarget.focus()
    }

    onSearchInput(event) {
        this._search(event.currentTarget.value)
        this.currentSelection = 0
    }

    onKeydown(event) {
        switch (event.code) {
        case 'ArrowDown':
            event.preventDefault()
            if (this.currentSelection === this.resultItemTargets.length) {
                return
            }
            this.currentSelection++
            this._updateSearchResults()
            break
        case 'ArrowUp':
            event.preventDefault()
            if (this.currentSelection < 2) {
                return
            }
            this.currentSelection--
            this._updateSearchResults()
            break
        case 'Enter':
            if (!this.currentSelection) {
                return
            }
            this._addItem(this.resultItemTargets[this.currentSelection - 1].dataset.id)
            break
        default:
            // console.log(event.code)
        }
    }

    clickOutside(event) {
        this.resultTarget.innerHTML = ''
    }

    _updateSearchResults() {
        this.resultItemTargets.forEach(function (e) {
            e.classList.remove('active')
        })
        this.resultItemTargets[this.currentSelection - 1].classList.add('active')
    }

    async _search(query) {
        if (!query) {
            this.resultTarget.innerHTML = ''
            return
        }
        const params = new URLSearchParams({
            q: query,
            excludes: JSON.stringify(this.agentsIds)
        })

        const response = await fetch(`${this.urlSearchValue}?${params.toString()}`)
        this.resultTarget.innerHTML = await response.text()
    }

    addItem(event){
        this._addItem(event.currentTarget.dataset.id)
    }

    removeItem(event) {
        event.currentTarget.parentNode.classList.add('removing');

        this._removeItem(event.currentTarget.dataset.id)
    }

    _addItem(id) {
        this.agentsIds.push(id)
        this._updateResult()
    }

    _removeItem(id) {
        this.agentsIds = this.agentsIds.filter(function (item) {
            return item !== id
        })
        this._updateResult()
    }

    async _updateResult(){
        const params = new URLSearchParams({
            agents: JSON.stringify(this.agentsIds),
        })

        this.resultTarget.innerHTML = ''

        const ids = this.agentsIds
        this.dispatch('update:view', {
            ids,
        })

        const response = await fetch(`${this.urlAgentListValue}?${params.toString()}`)
        this.agentListTarget.innerHTML = await response.text()

        this.inputTarget.value = ''
        this.inputTarget.focus()
    }
}
