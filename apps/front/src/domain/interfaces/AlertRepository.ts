import { Observable } from 'rxjs';
import { Alert } from '../entities/Alert';

export abstract class AlertRepository {
  abstract getAlerts(): Observable<Alert[]>;
  abstract discardAlert(alert: Alert): Observable<boolean>;
}
