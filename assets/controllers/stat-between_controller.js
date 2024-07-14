import { Controller } from '@hotwired/stimulus'

import datepicker from 'js-datepicker'
import 'js-datepicker/dist/datepicker.min.css'

export default class extends Controller {

    static values = {
        url: String,
        dates: Object,
        lastDate: String,
    }

    static targets = [
        'startDate', 'startTime',
        'endDate', 'endTime',
        'result',
    ]

    startDate = null
    endDate = null

    startPicker
    endPicker

    connect() {
        this.startPicker = datepicker(this.startDateTarget, {
            id: 1,
            onSelect: this.update.bind(this),
            disabler: this.checkDisabled.bind(this),
            startDate: new Date(this.lastDateValue)
        })
        this.endPicker = datepicker(this.endDateTarget, {
            id: 1,
            onSelect: this.update.bind(this),
            disabler: this.checkDisabled.bind(this),
            startDate: new Date(this.lastDateValue)
        })
    }

    checkDisabled(date) {
        const year = date.getFullYear()
        if (Object.hasOwn(this.datesValue, year)) {
            const month = date.getMonth() + 1
            if (Object.hasOwn(this.datesValue[year], month)) {
                const day = date.getDate()
                if (Object.hasOwn(this.datesValue[year][month], day)) {
                    return false
                }
            }
        }
        return true
    }

    timeChanged() {
        this._update()
    }

    update(instance, date) {
        const range = this.startPicker.getRange()
        if (range.start) {
            this._updateSelector(this.startTimeTarget, range.start)
        } else {
            this.startTimeTarget.innerHTML = ''
        }
        if (range.end) {
            this._updateSelector(this.endTimeTarget, range.end)
        } else {
            this.endTimeTarget.innerHTML = ''
        }
        this._update()
    }

    async _update() {
        const range = this.startPicker.getRange()
        if (!range.start || !range.end) {
            return
        }

        const params = new URLSearchParams({
            dateStart: this._getDateString(range.start, this.startTimeTarget.value),
            dateEnd: this._getDateString(range.end, this.endTimeTarget.value),
        })

        this.resultTarget.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span>'

        const response = await fetch(`${this.urlValue}?${params.toString()}`)
        this.resultTarget.innerHTML = await response.text()
    }

    _getDateString(date, time) {
        return `${date.getFullYear()}-${date.getMonth() + 1}-${date.getDate()} ${time}`
    }

    _updateSelector(selector, date) {
        const timeStamps = this.datesValue[date.getFullYear()][date.getMonth() + 1][date.getDate()]
        selector.innerHTML = ''
        timeStamps.reverse()
            .forEach(function (timeStamp) {
                const element = document.createElement('option')
                element.value = timeStamp
                element.innerText = timeStamp.slice(0, 5)
                selector.appendChild(element)
            })
    }
}
