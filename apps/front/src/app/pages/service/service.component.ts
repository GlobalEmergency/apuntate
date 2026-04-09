import { Component, OnInit } from '@angular/core';
import { ActivatedRoute, Router } from '@angular/router';
import { Service } from '../../../domain/entities/Service';
import { Gap } from '../../../domain/entities/Gap';
import { ServiceRepository } from '../../../domain/interfaces/ServiceRepository';
import { AuthenticationService } from '../../../services/authentication.service';

@Component({
  standalone: false,
  selector: 'app-service',
  templateUrl: './service.component.html',
  styleUrls: ['./service.component.scss'],
})
export class ServiceComponent implements OnInit {
  service: Service | null = null;
  gaps: Gap[] = [];
  loading = true;
  signupLoading: string | null = null;
  message: { text: string; type: 'success' | 'error' } | null = null;
  isAdmin = false;

  constructor(
    private route: ActivatedRoute,
    private router: Router,
    private authService: AuthenticationService,
    private serviceRepository: ServiceRepository,
  ) {}

  ngOnInit(): void {
    this.isAdmin = this.authService.checkAuthentication('ROLE_ADMIN');
    const id = this.route.snapshot.paramMap.get('id');
    if (!id) return;
    this.loadService(id);
  }

  private loadService(id: string): void {
    this.loading = true;
    this.serviceRepository.getService(id).subscribe({
      next: (service) => {
        this.service = service;
        this.loadGaps(id);
      },
      error: () => {
        this.loading = false;
        this.message = { text: 'No se pudo cargar el servicio.', type: 'error' };
      },
    });
  }

  private loadGaps(serviceId: string): void {
    this.serviceRepository.getServiceGaps(serviceId).subscribe({
      next: (gaps) => {
        this.gaps = gaps;
        this.loading = false;
      },
      error: () => {
        this.loading = false;
        this.message = { text: 'Error al cargar las plazas.', type: 'error' };
      },
    });
  }

  get currentUserId(): string {
    return this.authService.payload?.sub || '';
  }

  signup(gapId: string): void {
    if (!this.service) return;
    this.signupLoading = gapId;
    this.message = null;

    this.serviceRepository.signupForService(this.service.id, gapId).subscribe({
      next: () => {
        this.signupLoading = null;
        this.message = { text: 'Te has apuntado correctamente.', type: 'success' };
        this.loadGaps(this.service!.id);
      },
      error: (err) => {
        this.signupLoading = null;
        this.message = { text: err.error?.error || 'Error al apuntarse.', type: 'error' };
      },
    });
  }

  withdraw(gapId: string): void {
    if (!this.service) return;
    this.signupLoading = gapId;
    this.message = null;

    this.serviceRepository.withdrawFromService(this.service.id, gapId).subscribe({
      next: () => {
        this.signupLoading = null;
        this.message = { text: 'Has cancelado tu inscripción.', type: 'success' };
        this.loadGaps(this.service!.id);
      },
      error: (err) => {
        this.signupLoading = null;
        this.message = { text: err.error?.error || 'Error al cancelar.', type: 'error' };
      },
    });
  }

  publish(): void {
    if (!this.service) return;
    this.loading = true;
    this.message = null;

    this.serviceRepository.publishService(this.service.id).subscribe({
      next: () => {
        this.message = { text: 'Servicio publicado. Se ha notificado a todos los miembros.', type: 'success' };
        this.loading = false;
        this.loadService(this.service!.id);
      },
      error: (err) => {
        this.message = { text: err.error?.error || 'Error al publicar el servicio.', type: 'error' };
        this.loading = false;
      },
    });
  }

  edit(): void {
    if (!this.service) return;
    this.router.navigate(['/service', this.service.id, 'edit']);
  }

  cancelService(): void {
    if (!this.service) return;
    this.loading = true;
    this.serviceRepository.cancelService(this.service.id).subscribe({
      next: () => {
        this.message = { text: 'Servicio cancelado.', type: 'success' };
        this.loading = false;
        this.loadService(this.service!.id);
      },
      error: (err) => {
        this.message = { text: err.error?.error || 'Error al cancelar el servicio.', type: 'error' };
        this.loading = false;
      },
    });
  }
}
