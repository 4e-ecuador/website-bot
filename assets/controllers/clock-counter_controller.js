import { Controller } from '@hotwired/stimulus'

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static values = {
        deadline: String,
    }
    static targets = ['days', 'hours', 'minutes', 'seconds']
    timer = null

    connect() {
        this._update()
        this.timer = setInterval(this._update.bind(this), 1000)
    }

    _update() {
        const t = this._getTimeRemaining()
        if (t.total <= 0) {
            clearInterval(this.timer)

            return
        }
        this.daysTarget.innerText = t.days
        this.hoursTarget.innerText = t.hours
        this.minutesTarget.innerText = t.minutes
        this.secondsTarget.innerText = t.seconds
    }

    _getTimeRemaining() {
        let t = Date.parse(this.deadlineValue) - Date.parse(new Date())
        let seconds = Math.floor((t / 1000) % 60)
        let minutes = Math.floor((t / 1000 / 60) % 60)
        let hours = Math.floor((t / (1000 * 60 * 60)) % 24)
        let days = Math.floor(t / (1000 * 60 * 60 * 24))

        return {
            'total': t,
            'days': days,
            'hours': hours,
            'minutes': minutes,
            'seconds': seconds
        }
    }
}
