import { Injectable } from '@angular/core';
import { MatSnackBar } from '@angular/material/snack-bar';
import { BehaviorSubject, Observable } from 'rxjs';
import { Alert } from '../domain/entities/Alert';
import { AlertRepository } from '../domain/interfaces/AlertRepository';

@Injectable({ providedIn: 'root' })
export class AlertService {
  private alertsSubject = new BehaviorSubject<Alert[]>([]);
  private alerts$ = this.alertsSubject.asObservable();

  constructor(
    private snackBar: MatSnackBar,
    private alertRepository: AlertRepository,
  ) {
    this.loadInitialAlerts();
  }

  private loadInitialAlerts(): void {
    this.alertRepository.getAlerts().subscribe((alerts) => this.alertsSubject.next(alerts));
  }

  openSnackBar(message: string, action?: string): void {
    this.snackBar.open(message, action, { duration: 3000 });
  }

  getAlerts(): Observable<Alert[]> {
    return this.alerts$;
  }

  discardAlert(alert: Alert): void {
    const updatedAlerts = this.alertsSubject.value.map((a) => (a.id === alert.id ? { ...a, show: false } : a));

    // Update UI optimistically
    this.alertsSubject.next(updatedAlerts);

    this.alertRepository.discardAlert(alert).subscribe({
      error: () => {
        // Revert on failure
        this.alertsSubject.next(this.alertsSubject.value.map((a) => (a.id === alert.id ? { ...a, show: true } : a)));
      },
    });
  }
}
