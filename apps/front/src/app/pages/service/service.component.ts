import { Component, Inject } from '@angular/core';
import { ActivatedRoute, Router } from '@angular/router';
import { Service } from '../../../domain/Service';
import { ApiService } from '../../../services/api.service';
import { AuthenticationService } from '../../../services/authentication.service';

@Component({
  selector: 'app-service',
  templateUrl: './service.component.html',
  styleUrls: ['./service.component.scss'],
})
export class ServiceComponent {
  service: Service | null = null;
  gaps: any[] = [];
  loading = true;
  signupLoading: string | null = null;
  message: { text: string; type: 'success' | 'error' } | null = null;
  confirmingCancel = false;

  constructor(
    private route: ActivatedRoute,
    private router: Router,
    private authService: AuthenticationService,
    @Inject(ApiService) private apiService: ApiService,
  ) {}

  ngOnInit() {
    const id = this.route.snapshot.paramMap.get('id');
    if (!id) return;

    this.apiService.getService(id).subscribe({
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

  loadGaps(serviceId: string) {
    this.apiService.getServiceGaps(serviceId).subscribe({
      next: (gaps) => {
        this.gaps = gaps;
        this.loading = false;
      },
      error: () => {
        this.loading = false;
      },
    });
  }

  isAdmin(): boolean {
    return this.authService.checkAuthentication('ROLE_ADMIN');
  }

  currentUserEmail(): string {
    return this.authService.payload?.username || '';
  }

  isSignedUp(gap: any): boolean {
    return gap.user?.id === this.currentUserId();
  }

  currentUserId(): string {
    return this.authService.payload?.sub || '';
  }

  signup(gapId: string) {
    if (!this.service) return;
    this.signupLoading = gapId;
    this.message = null;

    this.apiService.signupForService(this.service.id, gapId).subscribe({
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

  withdraw(gapId: string) {
    if (!this.service) return;
    this.signupLoading = gapId;
    this.message = null;

    this.apiService.withdrawFromService(this.service.id, gapId).subscribe({
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

  filledGaps(): number {
    return this.gaps.filter((g) => g.user !== null).length;
  }

  edit() {
    if (!this.service) return;
    this.router.navigate(['/service', this.service.id, 'edit']);
  }

  cancelService() {
    if (!this.service || this.confirmingCancel) return;
    this.confirmingCancel = true;
  }

  confirmCancel() {
    if (!this.service) return;
    this.loading = true;
    this.apiService.cancelService(this.service.id).subscribe({
      next: () => {
        this.message = { text: 'Servicio cancelado.', type: 'success' };
        this.loading = false;
        this.confirmingCancel = false;
        this.ngOnInit();
      },
      error: (err) => {
        this.message = { text: err.error?.error || 'Error al cancelar el servicio.', type: 'error' };
        this.loading = false;
        this.confirmingCancel = false;
      },
    });
  }

  dismissCancel() {
    this.confirmingCancel = false;
  }
}
