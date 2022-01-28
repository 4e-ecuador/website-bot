import { Calendar } from "@fullcalendar/core";
// import { requestJson } from '@fullcalendar/core'

// import interactionPlugin from "@fullcalendar/interaction";
import dayGridPlugin from "@fullcalendar/daygrid";
// import timeGridPlugin from "@fullcalendar/timegrid";

// import "@fullcalendar/common/main.css";
// import "@fullcalendar/daygrid/main.css";
// import "@fullcalendar/timegrid/main.css";

// import "./index.css"; // this will create a calendar.css file reachable to 'encore_entry_link_tags'

document.addEventListener("DOMContentLoaded", () => {
    const calendarEl = document.getElementById('calendar-holder')
    const timeZoneSelectorEl = document.getElementById('time-zone-selector')

    const eventsUrl = calendarEl.dataset.eventsUrl
    const timeZone = calendarEl.dataset.timeZone

    const calendar = new Calendar(calendarEl, {
        eventSources: [
            {
                url: eventsUrl,
                method: 'POST',
                extraParams: {
                    start: 'now',
                    end: 'now',
                    filters: JSON.stringify({})
                },
                failure: () => {
                    // alert("There was an error while fetching FullCalendar!");
                },
            },
        ],
        // headerToolbar: {
        //     left: "prev,next today",
        //     center: "title",
        //     right: "dayGridMonth,timeGridWeek,timeGridDay",
        // },
        height: 'auto',
        // plugins: [interactionPlugin, dayGridPlugin, timeGridPlugin], // https://fullcalendar.io/docs/plugin-index
        plugins: [dayGridPlugin], // https://fullcalendar.io/docs/plugin-index
        locale: 'es',
        firstDay: 1,
        displayEventEnd: true,
        // timeZone: 'UTC',
        timeZone: 'local',
        // timeZone: 'America/Guayaquil',
        // timeZone: timeZone,
        eventTimeFormat: { hour: 'numeric', minute: '2-digit', timeZoneName: 'short' },
        buttonText: {
            today:    'Hoy',
        }
    })
    calendar.render();

    // load the list of available timezones, build the <select> options
    // it's highly encouraged to use your own AJAX lib instead of using FullCalendar's internal util
    // requestJson('GET', 'https://fullcalendar.io/demo-timezones.json', {}, function(timeZones) {
    //     timeZones.forEach(function(timeZone) {
    //         var optionEl;
    //
    //         if (timeZone !== 'UTC') { // UTC is already in the list
    //             optionEl = document.createElement('option');
    //             optionEl.value = timeZone;
    //             optionEl.innerText = timeZone;
    //             timeZoneSelectorEl.appendChild(optionEl);
    //         }
    //     });
    // }, function() {
    //     // failure
    // });

    timeZoneSelectorEl.addEventListener('change', function() {
        calendar.setOption('timeZone', this.value);
    });
});
