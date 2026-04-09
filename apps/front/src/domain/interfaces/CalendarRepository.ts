import { Observable } from 'rxjs';
import { EventInput } from '@fullcalendar/core';

export abstract class CalendarRepository {
  abstract getCalendar(start: Date, end: Date): Observable<EventInput[]>;
}
