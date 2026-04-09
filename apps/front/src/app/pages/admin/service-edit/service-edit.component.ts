import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ActivatedRoute, Router } from '@angular/router';
import { FormControl, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { MatCardModule } from '@angular/material/card';
import { MatInputModule } from '@angular/material/input';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatSelectModule } from '@angular/material/select';
import { MatOptionModule } from '@angular/material/core';
import { RouterModule } from '@angular/router';
import { FeedbackMessageComponent } from '../../../components/atoms/feedback-message/feedback-message.component';
import { SpinnerOverlayComponent } from '../../../components/atoms/spinner-overlay/spinner-overlay.component';
import { UnitManagerComponent } from '../../../components/organisms/unit-manager/unit-manager.component';
import { GapManagerComponent } from '../../../components/organisms/gap-manager/gap-manager.component';
import { ServiceRepository } from '../../../../domain/interfaces/ServiceRepository';
import { Service, ServiceCategory, ServicePriority, ServiceType } from '../../../../domain/entities/Service';
import { Gap } from '../../../../domain/entities/Gap';
import { KeyValuePipe } from '@angular/common';

@Component({
  selector: 'app-service-edit',
  standalone: true,
  imports: [
    CommonModule,
    ReactiveFormsModule,
    RouterModule,
    MatCardModule,
    MatInputModule,
    MatButtonModule,
    MatIconModule,
    MatSelectModule,
    MatOptionModule,
    KeyValuePipe,
    FeedbackMessageComponent,
    SpinnerOverlayComponent,
    UnitManagerComponent,
    GapManagerComponent,
  ],
  templateUrl: './service-edit.component.html',
  styleUrls: ['./service-edit.component.scss'],
})
export class ServiceEditComponent implements OnInit {
  loading = true;
  saving = false;
  service: Service | null = null;
  gaps: Gap[] = [];
  units: any[] = [];
  availableUnits: any[] = [];
  availableUnitComponents: any[] = [];
  message: { text: string; type: 'success' | 'error' | 'warning' } | null = null;

  form = new FormGroup({
    name: new FormControl('', { nonNullable: true, validators: [Validators.required] }),
    description: new FormControl(''),
    dateStart: new FormControl('', { nonNullable: true }),
    dateEnd: new FormControl('', { nonNullable: true }),
    datePlace: new FormControl('', { nonNullable: true }),
  });

  protected readonly ServiceCategory = ServiceCategory;
  protected readonly ServicePriority = ServicePriority;
  protected readonly ServiceType = ServiceType;

  constructor(
    private route: ActivatedRoute,
    private router: Router,
    private serviceRepository: ServiceRepository,
  ) {}

  ngOnInit(): void {
    const id = this.route.snapshot.paramMap.get('id');
    if (!id) return;
    this.loadService(id);
    this.loadAvailableUnits();
  }

  private loadService(id: string): void {
    this.loading = true;
    this.serviceRepository.getService(id).subscribe({
      next: (service) => {
        this.service = service;
        this.patchForm(service);
        this.units = (service as any).units || [];
        this.loadGaps(id);
        this.loading = false;
      },
      error: () => {
        this.loading = false;
        this.message = { text: 'No se pudo cargar el servicio.', type: 'error' };
      },
    });
  }

  private patchForm(service: Service): void {
    this.form.patchValue({
      name: service.name,
      description: service.description || '',
      dateStart: this.toDatetimeLocal(service.dateStart),
      dateEnd: this.toDatetimeLocal(service.dateEnd),
      datePlace: this.toDatetimeLocal(service.datePlace),
    });
  }

  private toDatetimeLocal(date: Date | string | null): string {
    if (!date) return '';
    const d = new Date(date);
    return d.toISOString().slice(0, 16);
  }

  private loadGaps(serviceId: string): void {
    this.serviceRepository.getServiceGaps(serviceId).subscribe({
      next: (gaps) => {
        this.gaps = gaps;
      },
    });
  }

  private loadAvailableUnits(): void {
    this.serviceRepository.getUnits().subscribe({
      next: (units) => {
        this.availableUnits = units;
        this.availableUnitComponents = [];
        units.forEach((u: any) => {
          (u.unitComponents || []).forEach((uc: any) => {
            this.availableUnitComponents.push({
              id: uc.id,
              name: uc.component?.name || 'Component',
              component: uc.component,
              unit: u,
            });
          });
        });
      },
    });
  }

  saveBasicInfo(): void {
    if (!this.form.valid || !this.service) {
      this.form.markAllAsTouched();
      this.message = { text: 'Completa los campos obligatorios.', type: 'error' };
      return;
    }

    this.saving = true;
    this.message = null;
    const data = this.form.value;

    this.serviceRepository
      .updateService(this.service.id, {
        name: data.name!,
        description: data.description || '',
        dateStart: data.dateStart ? new Date(data.dateStart) : undefined,
        dateEnd: data.dateEnd ? new Date(data.dateEnd) : undefined,
        datePlace: data.datePlace ? new Date(data.datePlace) : undefined,
      } as any)
      .subscribe({
        next: () => {
          this.saving = false;
          this.message = { text: 'Servicio actualizado.', type: 'success' };
        },
        error: (err) => {
          this.saving = false;
          this.message = { text: err.error?.error || 'Error al guardar.', type: 'error' };
        },
      });
  }

  onAddUnit(unitId: string): void {
    if (!this.service) return;
    this.saving = true;
    this.message = null;

    this.serviceRepository.addUnitToService(this.service.id, unitId).subscribe({
      next: () => {
        this.saving = false;
        this.message = { text: 'Unidad añadida y huecos generados.', type: 'success' };
        this.loadService(this.service!.id);
      },
      error: (err) => {
        this.saving = false;
        this.message = { text: err.error?.error || 'Error al añadir unidad.', type: 'error' };
      },
    });
  }

  onRemoveUnit(unitId: string): void {
    if (!this.service) return;
    this.saving = true;
    this.message = null;

    this.serviceRepository.removeUnitFromService(this.service.id, unitId).subscribe({
      next: () => {
        this.saving = false;
        this.message = { text: 'Unidad eliminada.', type: 'success' };
        this.loadService(this.service!.id);
      },
      error: (err) => {
        this.saving = false;
        this.message = { text: err.error?.error || 'Error al eliminar unidad.', type: 'error' };
      },
    });
  }

  onAddGap(unitComponentId: string): void {
    if (!this.service) return;
    this.saving = true;
    this.message = null;

    this.serviceRepository.addGapToService(this.service.id, unitComponentId).subscribe({
      next: () => {
        this.saving = false;
        this.message = { text: 'Hueco añadido.', type: 'success' };
        this.loadGaps(this.service!.id);
      },
      error: (err) => {
        this.saving = false;
        this.message = { text: err.error?.error || 'Error al añadir hueco.', type: 'error' };
      },
    });
  }

  onRemoveGap(gapId: string): void {
    if (!this.service) return;
    this.saving = true;
    this.message = null;

    this.serviceRepository.removeGapFromService(this.service.id, gapId).subscribe({
      next: () => {
        this.saving = false;
        this.message = { text: 'Hueco eliminado.', type: 'success' };
        this.loadGaps(this.service!.id);
      },
      error: (err) => {
        this.saving = false;
        this.message = { text: err.error?.error || 'Error al eliminar hueco.', type: 'error' };
      },
    });
  }

  goBack(): void {
    if (this.service) {
      this.router.navigate(['/service', this.service.id]);
    } else {
      this.router.navigate(['/dashboard']);
    }
  }
}
