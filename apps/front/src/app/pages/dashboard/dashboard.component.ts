import { Component, Inject, ViewEncapsulation } from '@angular/core';
import { ApiService } from '../../../services/api.service';
import { AuthenticationService } from '../../../services/authentication.service';
import { ServicesInterface } from '../../../domain/ServicesInterface';
import { Service } from '../../../domain/Service';
import { Router } from '@angular/router';

@Component({
    selector: 'app-dashboard',
    templateUrl: './dashboard.component.html',
    encapsulation: ViewEncapsulation.None,
    standalone: false
})
export class DashboardComponent {
  services: Service[] = [];
  userName = '';
  profile: any = null;

  constructor(
    @Inject(ApiService) private apiService: ApiService,
    private authService: AuthenticationService,
    private router: Router,
  ) {
    const payload = this.authService.payload;
    if (payload?.username) {
      this.userName = payload.username;
    }

    this.apiService.getNextEvents().subscribe((data) => {
      this.services = data;
    });

    this.apiService.getProfile().subscribe((data) => {
      this.profile = data;
      if (data.name) {
        this.userName = data.name + (data.surname ? ' ' + data.surname : '');
      }
    });
  }

  showService(service: Service) {
    this.router.navigate(['/service', service.id]);
  }
}
