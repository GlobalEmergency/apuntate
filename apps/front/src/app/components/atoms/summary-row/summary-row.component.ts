import { Component, Input } from '@angular/core';
import { CommonModule } from '@angular/common';

@Component({
  selector: 'app-summary-row',
  standalone: true,
  imports: [CommonModule],
  template: `
    <div class="summary-row">
      <span class="text-muted">{{ label }}</span>
      <span class="f-w-600" [ngClass]="colorClass">{{ value }}</span>
    </div>
  `,
  styles: [`
    .summary-row {
      display: flex;
      justify-content: space-between;
    }
  `],
})
export class SummaryRowComponent {
  @Input() label = '';
  @Input() value = '';
  @Input() colorClass = '';
}
