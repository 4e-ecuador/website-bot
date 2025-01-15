import { Controller } from '@hotwired/stimulus'

import '../css/account.css'

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static targets = ['lat', 'lon']

    /**
     * This will receive the detail from the account-map:update event and update the
     * form field values accordingly.
     */
    update({ detail: { center } }) {
        this.latTarget.value = center.lat
        this.lonTarget.value = center.lng
    }

    /**
     * This will update a set of CSS classes for images according to the state of
     * some input option form fields.
     */
    updateMedals(e) {
        let element

        if ('SPAN' === e.target.tagName) {
            element = e.target.parentElement
        } else {
            element = e.target
        }

        let input = document.getElementById(element.dataset.for)

        if (input.checked) {
            input.checked = false
            element.classList.remove('medalSelected')
        } else {
            Array.prototype.forEach.call(document.getElementsByName(input.name), function (el) {
                document.querySelector('label[data-for=' + el.id + ']')
                    .classList
                    .remove('medalSelected')
            })

            input.checked = true
            element.classList.add('medalSelected')
        }
    }
}
