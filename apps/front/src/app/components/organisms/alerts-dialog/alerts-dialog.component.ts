import { Component, OnDestroy } from '@angular/core';
import { CommonModule } from '@angular/common';
import { MatCardModule } from '@angular/material/card';
import { MatIconModule } from '@angular/material/icon';
import { MatButtonModule } from '@angular/material/button';
import { AlertService } from '../../../../services/alert.service';
import { Alert } from '../../../../domain/entities/Alert';
import { Subscription } from 'rxjs';

@Component({
  selector: 'app-alerts-dialog',
  standalone: true,
  imports: [CommonModule, MatCardModule, MatIconModule, MatButtonModule],
  templateUrl: './alerts-dialog.component.html',
  styleUrls: ['./alerts-dialog.component.scss'],
})
export class AlertsDialogComponent implements OnDestroy {
  alerts: Alert[] = [];
  private alertsSub: Subscription;

  constructor(private alertService: AlertService) {
    this.alertsSub = this.alertService.getAlerts().subscribe((alerts) => {
      this.alerts = alerts;
    });
  }

  ngOnDestroy(): void {
    this.alertsSub.unsubscribe();
  }

  discardAlert(alert: Alert): void {
    this.alertService.discardAlert(alert);
  }
}
