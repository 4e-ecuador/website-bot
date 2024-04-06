import { Controller } from '@hotwired/stimulus'

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static values = {
        sendMailUrl: String,
    }

    static targets = ['resultContainer']

    async sendMail() {
        this.resultContainerTarget.innerText = 'Sending email...'

        try {
            const response = await fetch(this.sendMailUrlValue)

            if (response.ok) {
                this.resultContainerTarget.innerText = await response.text()
            } else {
                switch (response.status) {
                case 404:
                    this.resultContainerTarget.innerText = 'Not found :('
                    break
                default:
                    this.resultContainerTarget.innerText = `Unknown server error occured: ${await response.text()}`
                }
            }
        } catch (error) {
            const msg = `Something went wrong: ${error.message || error}`
            this.resultContainerTarget.innerText = msg
            throw new Error(msg)
        }
    }
}
