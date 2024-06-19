import { Controller } from '@hotwired/stimulus'

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    resetAndSubmit() {
        this._setValue('page', 1)
        this._setValue('order', '')
        this._setValue('orderDir', '')

        this.element.submit()
    }

    goToPage(e) {
        this._setValue('page', e.params.page)
    }

    setOrdering(e) {
        this._setValue('order', e.params.order)
        this._setValue('orderDir', e.params.orderDir)
    }

    cleanPreviousAndSubmit(e) {
        e.target.parentNode.parentNode.previousElementSibling.value = ''
    }

    _setValue(element, value) {
        this.element.querySelector('input[name="paginatorOptions[' + element + ']"]').value = value
    }
}
