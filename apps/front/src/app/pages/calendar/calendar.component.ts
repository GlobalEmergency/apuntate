import { Component, ViewEncapsulation } from '@angular/core';
import { CalendarOptions } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import { CalendarRepository } from '../../../domain/interfaces/CalendarRepository';

@Component({
  selector: 'app-calendar',
  templateUrl: './calendar.component.html',
  encapsulation: ViewEncapsulation.None,
  standalone: false,
})
export class CalendarComponent {
  calendarOptions: CalendarOptions = {
    plugins: [dayGridPlugin],
    initialView: 'dayGridMonth',
    firstDay: 1,
    weekends: true,
    events: (fetchInfo, successCallback, failureCallback) => {
      this.calendarRepository.getCalendar(fetchInfo.start, fetchInfo.end).subscribe({
        next: events => {
          events.forEach(event => { event.url = '/service/' + event.id; });
          successCallback(events);
        },
        error: error => failureCallback(error),
      });
    },
  };

  constructor(private calendarRepository: CalendarRepository) {}
}
