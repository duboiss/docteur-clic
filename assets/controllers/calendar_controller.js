import { Controller } from '@hotwired/stimulus';
import { Calendar } from 'https://cdn.skypack.dev/@fullcalendar/core@6.1.14';
import frLocale from 'https://cdn.skypack.dev/@fullcalendar/core@6.1.14/locales/fr.js';
import dayGridPlugin from 'https://cdn.skypack.dev/@fullcalendar/daygrid@6.1.14';

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static values = { events: String }

    connect() {
        this.initializeCalendar()
    }

    initializeCalendar() {
        const calendarEl = document.getElementById('calendar');

        const eventsTest = JSON.parse(this.eventsValue).map(event => ({
            ...event,
            start: new Date(event.start),
            end: new Date(event.end)
        }));

        const calendar = new Calendar(calendarEl, {
            locale: frLocale,
            plugins: [dayGridPlugin],
            initialView: 'dayGridMonth',
            events: eventsTest,
        });

        calendar.render();
    }
}
