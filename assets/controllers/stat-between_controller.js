import { Controller } from '@hotwired/stimulus'
import datepicker from 'js-datepicker'
import 'js-datepicker/dist/datepicker.min.css'

export default class extends Controller {
    static values = {
        url: String,
    }
    static targets = ['result', 'newDateStart']

    startDate = null
    endDate = null

    connect() {
        const picker = datepicker(this.newDateStartTarget)
    }

    async dateChanged({
        detail: {
            item,
            year,
            month,
            day,
            time
        }
    })
    {
        const times = time.split(':')
        const date = new Date(year, month - 1, day, parseInt(times[0]), parseInt(times[1]), parseInt(times[2]))
        if ('start' === item) {
            this.startDate = date
        } else if ('end' === item) {
            this.endDate = date
        } else {
            console.error('unknown item: ' + item)
        }

        if (this.startDate > this.endDate) {
            this.resultTarget.innerText = 'start is greater than end'
        } else if (this.startDate.getTime() === this.endDate.getTime()) {
            this.resultTarget.innerText = 'Dates are the same :('
        } else {
            const params = new URLSearchParams({
                dateStart: this._getDateString(this.startDate),
                dateEnd: this._getDateString(this.endDate),
            })

            this.resultTarget.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span>'

            const response = await fetch(`${this.urlValue}?${params.toString()}`)
            this.resultTarget.innerHTML = await response.text()
        }
    }

    _getDateString(date) {
        return date.getFullYear()
            + '-' + (date.getMonth()+1)
            + '-' + (date.getDate())
            + ' ' + (date.getHours())
            + ':' + (date.getMinutes())
            + ':' + (date.getSeconds())
    }
}
