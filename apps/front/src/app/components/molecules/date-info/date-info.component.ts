import { ChangeDetectionStrategy, Component, Input } from '@angular/core';
import { CommonModule } from '@angular/common';

@Component({
  selector: 'app-date-info',
  standalone: true,
  imports: [CommonModule],
  template: `
    <div>
      <span class="mat-body-2 text-muted">{{ label }}</span>
      <p class="mat-body-1">{{ date | date: 'dd/MM/yyyy HH:mm' }}</p>
    </div>
  `,
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class DateInfoComponent {
  @Input() label = '';
  @Input() date: Date | string | null = null;
}
