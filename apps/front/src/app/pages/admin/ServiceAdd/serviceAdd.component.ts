import { Component, Input, OnInit } from '@angular/core';
import { MatInputModule } from '@angular/material/input';
import { MatIconModule } from '@angular/material/icon';
import { FormControl, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { KeyValuePipe, NgForOf, NgIf } from '@angular/common';
import {
  Service,
  ServiceForm,
  ServiceCategory,
  ServicePriority,
  ServiceStatus,
  ServiceType,
} from '../../../../domain/entities/Service';
import { MatOptionModule } from '@angular/material/core';
import { MatSelectModule } from '@angular/material/select';
import { MatButtonModule } from '@angular/material/button';
import { ServiceRepository } from '../../../../domain/interfaces/ServiceRepository';
import { v4 as uuidv4 } from 'uuid';
import { Router } from '@angular/router';
import { FeedbackMessageComponent } from '../../../components/atoms/feedback-message/feedback-message.component';

@Component({
  selector: 'app-service-add',
  templateUrl: './serviceAdd.component.html',
  styleUrls: ['./serviceAdd.component.scss'],
  imports: [
    MatInputModule, MatIconModule, ReactiveFormsModule,
    NgIf, MatOptionModule, MatSelectModule, NgForOf, MatButtonModule,
    KeyValuePipe, FeedbackMessageComponent,
  ],
})
export class ServiceAddComponent implements OnInit {
  @Input() service!: Service;

  serviceForm!: FormGroup<ServiceForm>;
  message: { text: string; type: 'success' | 'error' } | null = null;

  constructor(
    private serviceRepository: ServiceRepository,
    private router: Router,
  ) {}

  ngOnInit(): void {
    if (!this.service) {
      this.service = new Service(
        uuidv4(), '', '', new Date(), new Date(), new Date(),
        ServiceStatus.DRAFT, [], [],
        ServiceCategory.PREVENTIVE, ServicePriority.MEDIUM, ServiceType.COVERAGE,
      );
    }

    this.serviceForm = new FormGroup<ServiceForm>({
      id: new FormControl(this.service.id, { nonNullable: true }),
      name: new FormControl(this.service.name, { nonNullable: true, validators: [Validators.required] }),
      description: new FormControl(this.service.description),
      category: new FormControl<ServiceCategory>(this.service.category as ServiceCategory, { nonNullable: true }),
      priority: new FormControl(this.service.priority, { nonNullable: true }),
      type: new FormControl(this.service.type, { nonNullable: true }),
      status: new FormControl(this.service.status, { nonNullable: true }),
      dateStart: new FormControl(this.service.dateStart, { nonNullable: true }),
      dateEnd: new FormControl(this.service.dateEnd, { nonNullable: true }),
      datePlace: new FormControl(this.service.datePlace, { nonNullable: true }),
      units: new FormControl(this.service.units, { nonNullable: true }),
      gaps: new FormControl(this.service.gaps, { nonNullable: true }),
    });
  }

  sendForm(): void {
    if (!this.serviceForm.valid) {
      this.serviceForm.markAllAsTouched();
      this.message = { text: 'Completa los campos obligatorios.', type: 'error' };
      return;
    }
    this.message = null;

    this.service = Service.fromForm(this.serviceForm.value);
    this.serviceRepository.addService(this.service).subscribe({
      next: () => {
        this.router.navigate(['/service/' + this.service.id]);
      },
      error: (err) => {
        this.message = { text: err.error?.error || 'Error al crear el servicio.', type: 'error' };
      },
    });
  }

  protected readonly ServiceCategory = ServiceCategory;
  protected readonly ServicePriority = ServicePriority;
  protected readonly ServiceType = ServiceType;
}
