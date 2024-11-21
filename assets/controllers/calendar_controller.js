import { Controller } from '@hotwired/stimulus'

import 'fullcalendar'; //this is needed.
import { Calendar } from '@fullcalendar/core'
import dayGridPlugin from '@fullcalendar/daygrid'

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static values = {
        eventsUrl: String,
        defaultTimeZone: String,
    }
    static targets = ['timeZoneSelector', 'frame']

    connect() {
        const calendar = new Calendar(this.frameTarget, {
            eventSources: [
                {
                    url: this.eventsUrlValue,
                    method: 'POST',
                    extraParams: {
                        start: 'now',
                        end: 'now',
                        filters: JSON.stringify({})
                    },
                    failure: () => {
                        alert('There was an error while fetching FullCalendar!')
                    },
                },
            ],
            height: 'auto',
            plugins: [dayGridPlugin], // https://fullcalendar.io/docs/plugin-index
            locale: 'es',
            firstDay: 1,
            displayEventEnd: true,
            timeZone: 'local',
            eventTimeFormat: {
                hour: 'numeric',
                minute: '2-digit',
                timeZoneName: 'short'
            },
            buttonText: {
                today: 'Hoy',
            }
        })
        calendar.render()

        this.timeZoneSelectorTarget.addEventListener('change', function () {
            calendar.setOption('timeZone', this.value)
        })
    }
}
