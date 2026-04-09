import { Component, Input } from '@angular/core';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';

@Component({
  selector: 'app-spinner-overlay',
  standalone: true,
  imports: [MatProgressSpinnerModule],
  template: `
    <div class="spinner-overlay">
      <mat-progress-spinner mode="indeterminate" [diameter]="diameter"></mat-progress-spinner>
    </div>
  `,
  styles: [
    `
      .spinner-overlay {
        display: flex;
        justify-content: center;
        padding: 48px;
      }
    `,
  ],
})
export class SpinnerOverlayComponent {
  @Input() diameter = 40;
}
