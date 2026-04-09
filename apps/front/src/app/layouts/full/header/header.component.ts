import { Component, Output, EventEmitter, Input, ViewEncapsulation, OnDestroy } from '@angular/core';
import { MatDialog } from '@angular/material/dialog';
import { AuthenticationService } from '../../../../services/authentication.service';
import { Router } from '@angular/router';
import { AlertService } from '../../../../services/alert.service';
import { AlertsDialogComponent } from '../../../components/organisms/alerts-dialog/alerts-dialog.component';
import { Subscription } from 'rxjs';

@Component({
  standalone: false,
  selector: 'app-header',
  templateUrl: './header.component.html',
  encapsulation: ViewEncapsulation.None,
})
export class HeaderComponent implements OnDestroy {
  @Input() showToggle = true;
  @Input() toggleChecked = false;
  @Output() toggleMobileNav = new EventEmitter<void>();
  @Output() toggleMobileFilterNav = new EventEmitter<void>();
  @Output() toggleCollapsed = new EventEmitter<void>();

  alertsActive = 0;
  private alertsSub: Subscription;

  constructor(
    public dialog: MatDialog,
    public authService: AuthenticationService,
    public router: Router,
    public alertService: AlertService,
  ) {
    this.alertsSub = this.alertService.getAlerts().subscribe((alerts) => {
      this.alertsActive = alerts.filter((alert) => alert.show).length;
    });
  }

  ngOnDestroy(): void {
    this.alertsSub.unsubscribe();
  }

  showAlerts(): void {
    this.dialog.open(AlertsDialogComponent, {
      width: '100%',
      panelClass: 'alerts-dialog',
    });
  }

  closeSession(): void {
    this.authService.logout();
  }
}
