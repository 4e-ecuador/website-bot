import { Controller } from '@hotwired/stimulus'

/*
 * Copies the source target's value to the clipboard and briefly shows the success text on the button.
 */
/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static targets = ['source', 'button']
    static values = { successContent: String, successDuration: { type: Number, default: 2000 } }

    async copy(event) {
        event.preventDefault()

        await navigator.clipboard.writeText(this.sourceTarget.value)

        if (!this.hasButtonTarget || !this.successContentValue) {
            return
        }

        const original = this.buttonTarget.innerHTML
        this.buttonTarget.innerHTML = this.successContentValue

        setTimeout(() => {
            this.buttonTarget.innerHTML = original
        }, this.successDurationValue)
    }
}
