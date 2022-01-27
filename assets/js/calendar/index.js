import { Calendar } from "@fullcalendar/core";
import interactionPlugin from "@fullcalendar/interaction";
import dayGridPlugin from "@fullcalendar/daygrid";
import timeGridPlugin from "@fullcalendar/timegrid";

import "@fullcalendar/common/main.css";
import "@fullcalendar/daygrid/main.css";
import "@fullcalendar/timegrid/main.css";

// import "./index.css"; // this will create a calendar.css file reachable to 'encore_entry_link_tags'

document.addEventListener("DOMContentLoaded", () => {
    const calendarEl = document.getElementById('calendar-holder')

    const eventsUrl = calendarEl.dataset.eventsUrl

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
        height: '100%',
        plugins: [interactionPlugin, dayGridPlugin, timeGridPlugin], // https://fullcalendar.io/docs/plugin-index
        locale: 'es',
        firstDay: 1,
        timeZone: 'UTC',
        buttonText: {
            today:    'Hoy',
        }
    })
    calendar.render();
});
