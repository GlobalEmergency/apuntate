import { Component, DestroyRef, inject, OnInit } from '@angular/core';
import { takeUntilDestroyed } from '@angular/core/rxjs-interop';
import { Router } from '@angular/router';
import { forkJoin } from 'rxjs';
import { Service } from '../../../domain/entities/Service';
import { ServiceRepository } from '../../../domain/interfaces/ServiceRepository';
import { AdminRepository } from '../../../domain/interfaces/AdminRepository';
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
  loading = true;
  message: { text: string; type: 'success' | 'error' } | null = null;

  private destroyRef = inject(DestroyRef);

  constructor(
    private serviceRepository: ServiceRepository,
    private adminRepository: AdminRepository,
    private authService: AuthenticationService,
    private router: Router,
  ) {}

  ngOnInit(): void {
    const payload = this.authService.payload;
    if (payload?.username) {
      this.userName = payload.username;
    }

    forkJoin({
      services: this.serviceRepository.getNextEvents(),
      profile: this.adminRepository.getProfile(),
    })
      .pipe(takeUntilDestroyed(this.destroyRef))
      .subscribe({
        next: ({ services, profile }) => {
          this.services = services;
          this.profile = profile;
          if (profile.name) {
            this.userName = profile.name + (profile.surname ? ' ' + profile.surname : '');
          }
          this.loading = false;
        },
        error: () => {
          this.loading = false;
          this.message = { text: 'Error al cargar los datos.', type: 'error' };
        },
      });
  }

  showService(service: Service): void {
    this.router.navigate(['/service', service.id]);
  }
}
