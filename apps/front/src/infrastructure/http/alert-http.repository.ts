import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../environments/environment';
import { AlertRepository } from '../../domain/interfaces/AlertRepository';
import { Alert } from '../../domain/entities/Alert';

@Injectable()
export class AlertHttpRepository extends AlertRepository {
  private readonly url = environment.api_url;

  constructor(private http: HttpClient) {
    super();
  }

  getAlerts(): Observable<Alert[]> {
    return this.http.get<Alert[]>(`${this.url}/alerts`);
  }

  discardAlert(alert: Alert): Observable<boolean> {
    return this.http.post<boolean>(`${this.url}/alerts/${alert.id}`, alert);
  }
}
