import { Component, Input } from '@angular/core';
import { CommonModule } from '@angular/common';
import { MatIconModule } from '@angular/material/icon';

@Component({
  selector: 'app-stat-icon',
  standalone: true,
  imports: [CommonModule, MatIconModule],
  templateUrl: './stat-icon.component.html',
  styleUrls: ['./stat-icon.component.scss'],
})
export class StatIconComponent {
  @Input() icon = '';
  @Input() color: 'primary' | 'accent' = 'primary';
  @Input() value = '';
  @Input() label = '';
}
