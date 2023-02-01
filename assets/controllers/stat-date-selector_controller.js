import { Controller } from '@hotwired/stimulus'

export default class extends Controller {
    static values = {
        dates: Object,
        item: String,
    }

    static targets = ['year', 'month', 'day', 'time']

    connect() {
        this.setYear()
    }

    setYear() {
        this._updateSelector(this.yearTarget, Object.keys(this.datesValue))
        this.setMonth()
    }

    setMonth() {
        this._updateSelector(this.monthTarget, Object.keys(this.datesValue[this.yearTarget.value]))
        this.setDay()
    }

    setDay() {
        this._updateSelector(this.dayTarget, Object.keys(this.datesValue[this.yearTarget.value][this.monthTarget.value]))
        this.setTime()
    }

    setTime() {
        this._updateSelector(this.timeTarget, this.datesValue[this.yearTarget.value][this.monthTarget.value][this.dayTarget.value])
        this.dateChanged()
    }

    _updateSelector(selector, array) {
        selector.innerHTML = ''
        array.forEach(function (val) {
            const option = document.createElement('option')
            option.value = val
            option.innerText = val
            selector.appendChild(option)
        })
    }

    dateChanged(){
        this.dispatch(
            "dateChanged",
            {
                detail: {
                    item: this.itemValue,
                    year: this.yearTarget.value,
                    month: this.monthTarget.value,
                    day: this.dayTarget.value,
                    time: this.timeTarget.value,
                }
            }
        )
    }
}
