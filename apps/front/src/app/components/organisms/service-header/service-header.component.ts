import { ChangeDetectionStrategy, Component, Input, Output, EventEmitter } from '@angular/core';
import { CommonModule } from '@angular/common';
import { MatCardModule } from '@angular/material/card';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatChipsModule } from '@angular/material/chips';
import { DateInfoComponent } from '../../molecules/date-info/date-info.component';
import { ConfirmActionComponent } from '../../molecules/confirm-action/confirm-action.component';
import { Service } from '../../../../domain/entities/Service';

@Component({
  selector: 'app-service-header',
  standalone: true,
  imports: [
    CommonModule,
    MatCardModule,
    MatButtonModule,
    MatIconModule,
    MatChipsModule,
    DateInfoComponent,
    ConfirmActionComponent,
  ],
  templateUrl: './service-header.component.html',
  styleUrls: ['./service-header.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ServiceHeaderComponent {
  @Input() service!: Service;
  @Input() isAdmin = false;
  @Input() loading = false;
  @Output() publish = new EventEmitter<void>();
  @Output() edit = new EventEmitter<void>();
  @Output() cancelService = new EventEmitter<void>();

  confirmingCancel = false;

  onCancelRequest(): void {
    this.confirmingCancel = true;
  }

  onConfirmCancel(): void {
    this.confirmingCancel = false;
    this.cancelService.emit();
  }

  onDismissCancel(): void {
    this.confirmingCancel = false;
  }
}
