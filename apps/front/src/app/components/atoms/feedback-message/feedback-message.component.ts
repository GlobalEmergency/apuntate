import { Component, Input } from '@angular/core';
import { CommonModule } from '@angular/common';
import { MatIconModule } from '@angular/material/icon';

@Component({
  selector: 'app-feedback-message',
  standalone: true,
  imports: [CommonModule, MatIconModule],
  templateUrl: './feedback-message.component.html',
  styleUrls: ['./feedback-message.component.scss'],
})
export class FeedbackMessageComponent {
  @Input() message: { text: string; type: 'success' | 'error' | 'warning' } | null = null;

  get iconName(): string {
    if (!this.message) return '';
    switch (this.message.type) {
      case 'success':
        return 'check_circle';
      case 'warning':
        return 'warning';
      default:
        return 'error';
    }
  }
}
