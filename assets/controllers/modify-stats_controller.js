import { Controller } from '@hotwired/stimulus'

export default class extends Controller {

    static targets = [
        'input', 'output', 'formFields', 'form'
    ]

    static values = {
        urlInput: String,
    }

    async processInput(event) {
        const params = new URLSearchParams({
            q: this.inputTarget.value,
            preview: 1,
        })
        const response = await fetch(`${this.urlInputValue}?${params.toString()}`)
        this.formFieldsTarget.innerHTML = await response.text()
    }

    processFields(event) {
        event.preventDefault()

        let headers, values
        const inputs = this.formTarget.getElementsByTagName('input')

        for (const i of inputs) {
            if (headers && values) {
                headers += '\t' + i.id
                values += '\t' + i.value
            } else {
                headers = i.id
                values = i.value
            }
        }

        this.outputTarget.innerHTML = headers + "\n" + values
    }
}
