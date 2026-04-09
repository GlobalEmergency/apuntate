import { Component, OnInit } from '@angular/core';
import { Router } from '@angular/router';
import { Service } from '../../../domain/entities/Service';
import { ServiceRepository } from '../../../domain/interfaces/ServiceRepository';
import { ProfileRepository } from '../../../domain/interfaces/ProfileRepository';
import { AuthenticationService } from '../../../services/authentication.service';

@Component({
  standalone: false,
  selector: 'app-dashboard',
  templateUrl: './dashboard.component.html',
  styleUrls: ['./dashboard.component.scss'],
})
export class DashboardComponent implements OnInit {
  services: Service[] = [];
  userName = '';
  profile: any = null;
  message: { text: string; type: 'success' | 'error' } | null = null;

  constructor(
    private serviceRepository: ServiceRepository,
    private profileRepository: ProfileRepository,
    private authService: AuthenticationService,
    private router: Router,
  ) {}

  ngOnInit(): void {
    const payload = this.authService.payload;
    if (payload?.username) {
      this.userName = payload.username;
    }

    this.serviceRepository.getNextEvents().subscribe({
      next: (data) => {
        this.services = data;
      },
      error: () => {
        this.message = { text: 'Error al cargar los servicios.', type: 'error' };
      },
    });

    this.profileRepository.getProfile().subscribe({
      next: (data) => {
        this.profile = data;
        if (data.name) {
          this.userName = data.name + (data.surname ? ' ' + data.surname : '');
        }
      },
      error: () => {
        this.message = { text: 'Error al cargar el perfil.', type: 'error' };
      },
    });
  }

  showService(service: Service): void {
    this.router.navigate(['/service', service.id]);
  }
}
