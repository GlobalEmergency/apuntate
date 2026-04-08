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
})
export class DashboardComponent {
  services: Service[] = [];
  userName = '';

  constructor(
    @Inject(ApiService) private serviceRepository: ServicesInterface,
    private authService: AuthenticationService,
    private router: Router,
  ) {
    const payload = this.authService.payload;
    if (payload?.username) {
      this.userName = payload.username;
    }

    this.serviceRepository.getNextEvents().subscribe((data) => {
      this.services = data;
    });
  }

  showService(service: Service) {
    this.router.navigate(['/service', service.id]);
  }
}
