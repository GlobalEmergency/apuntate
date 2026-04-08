import { Component, Input } from '@angular/core';
import { CommonModule } from '@angular/common';
import { MatIconModule } from '@angular/material/icon';

@Component({
  selector: 'app-feedback-message',
  standalone: true,
  imports: [CommonModule, MatIconModule],
  template: `
    <div *ngIf="message"
         [style.background]="message.type === 'success' ? '#e8f5e9' : '#fce4ec'"
         [style.color]="message.type === 'success' ? '#2e7d32' : '#c62828'"
         style="border-radius: 8px; display: flex; align-items: center; gap: 8px; padding: 12px 16px; font-size: 14px;">
      <mat-icon>{{ message.type === 'success' ? 'check_circle' : 'error' }}</mat-icon>
      <span>{{ message.text }}</span>
    </div>
  `,
})
export class FeedbackMessageComponent {
  @Input() message: { text: string; type: 'success' | 'error' } | null = null;
}
