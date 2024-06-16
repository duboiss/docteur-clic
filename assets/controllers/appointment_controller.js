import { Controller } from '@hotwired/stimulus';
import flatpickr from 'flatpickr';
import 'flatpickr/dist/flatpickr.min.css';

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static values = {
        doctorAppointments: String
    }

    connect() {
        const today = new Date();
        const minDate = getNextWeekday(today);
        let maxDate = new Date();
        maxDate.setFullYear(today.getFullYear() + 1);

        flatpickr('.js-datetime-picker', {
            enableTime: true,
            noCalendar: false,
            time_24hr: true,
            locale: {
                firstDayOfWeek: 1
            },
            minuteIncrement: 0,
            defaultHour: 7,
            defaultMinute: 0,
            defaultDate: minDate,
            minDate: minDate,
            maxDate: maxDate,
            minTime: "07:00",
            maxTime: "18:00",
            disable: [
                function(date) {
                    // Disable weekends
                    return date.getDay() === 0 || date.getDay() === 6;
                }
            ],
        });
    }
}

function getNextWeekday(date) {
    let nextWeek = new Date(date);
    nextWeek.setDate(nextWeek.getDate() + 7);
    if (nextWeek.getDay() === 6) { // If it's Saturday
        nextWeek.setDate(nextWeek.getDate() + 2);
    } else if (nextWeek.getDay() === 0) { // If it's Sunday
        nextWeek.setDate(nextWeek.getDate() + 1);
    }
    nextWeek.setHours(7, 0, 0, 0); // Set hours and minutes to zero

    return nextWeek;
}
