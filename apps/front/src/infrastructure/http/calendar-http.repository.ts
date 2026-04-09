import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../environments/environment';
import { CalendarRepository } from '../../domain/interfaces/CalendarRepository';
import { EventInput } from '@fullcalendar/core';

@Injectable()
export class CalendarHttpRepository extends CalendarRepository {
  private readonly url = environment.api_url;

  constructor(private http: HttpClient) {
    super();
  }

  getCalendar(start: Date, end: Date): Observable<EventInput[]> {
    return this.http.get<EventInput[]>(`${this.url}/services/calendar?s=${start.toISOString()}&e=${end.toISOString()}`);
  }
}
