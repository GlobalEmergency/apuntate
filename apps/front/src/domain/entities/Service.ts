import { FormControl } from '@angular/forms';
import { Gap } from './Gap';

export interface ServiceForm {
  id: FormControl<string>;
  name: FormControl<string>;
  description: FormControl<string | null>;
  category: FormControl<ServiceCategory>;
  priority: FormControl<ServicePriority>;
  type: FormControl<ServiceType>;
  status: FormControl<ServiceStatus>;
  dateStart: FormControl<Date>;
  dateEnd: FormControl<Date>;
  datePlace: FormControl<Date>;
  units: FormControl<Gap[]>;
  gaps: FormControl<Gap[]>;
}

export enum ServiceStatus {
  DRAFT = 'draft',
  CONFIRMED = 'confirmed',
  CANCELLED = 'cancelled',
  FINISHED = 'finished',
}

export enum ServiceCategory {
  PREVENTIVE = 'preventive',
  EMERGENCY = 'emergency',
  TRAINING = 'training',
}

export enum ServicePriority {
  LOW = 'low',
  MEDIUM = 'medium',
  HIGH = 'high',
}

export enum ServiceType {
  COVERAGE = 'coverage',
  SUPPORT = 'support',
  LOGISTICS = 'logistics',
}

export class Service {
  readonly id: string;
  readonly name: string;
  readonly description: string;
  readonly dateStart: Date;
  readonly dateEnd: Date;
  readonly datePlace: Date;
  readonly status: ServiceStatus;
  readonly units: Gap[];
  readonly gaps: Gap[];
  readonly category: ServiceCategory;
  readonly priority: ServicePriority;
  readonly type: ServiceType;

  constructor(
    id = '',
    name = '',
    description = '',
    dateStart: Date = new Date(),
    dateEnd: Date = new Date(),
    datePlace: Date = new Date(),
    status: ServiceStatus = ServiceStatus.DRAFT,
    units: Gap[] = [],
    gaps: Gap[] = [],
    category: ServiceCategory = ServiceCategory.PREVENTIVE,
    priority: ServicePriority = ServicePriority.MEDIUM,
    type: ServiceType = ServiceType.COVERAGE,
  ) {
    this.id = id;
    this.name = name;
    this.description = description;
    this.dateStart = dateStart;
    this.dateEnd = dateEnd;
    this.datePlace = datePlace;
    this.status = status;
    this.units = units;
    this.gaps = gaps;
    this.category = category;
    this.priority = priority;
    this.type = type;
  }

  static fromForm(
    form: Partial<{ [K in keyof ServiceForm]: ServiceForm[K] extends FormControl<infer V> ? V : never }>,
  ): Service {
    return new Service(
      form.id,
      form.name,
      form.description ?? '',
      form.dateStart,
      form.dateEnd,
      form.datePlace,
      form.status,
      form.units,
      form.gaps,
      form.category,
      form.priority,
      form.type,
    );
  }
}
