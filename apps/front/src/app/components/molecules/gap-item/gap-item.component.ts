import { Component, Input, Output, EventEmitter } from '@angular/core';
import { CommonModule } from '@angular/common';
import { MatIconModule } from '@angular/material/icon';
import { MatButtonModule } from '@angular/material/button';
import { MatChipsModule } from '@angular/material/chips';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { Gap } from '../../../../domain/entities/Gap';

@Component({
  selector: 'app-gap-item',
  standalone: true,
  imports: [CommonModule, MatIconModule, MatButtonModule, MatChipsModule, MatProgressSpinnerModule],
  templateUrl: './gap-item.component.html',
  styleUrls: ['./gap-item.component.scss'],
})
export class GapItemComponent {
  @Input() gap!: Gap;
  @Input() isCurrentUser = false;
  @Input() loading = false;
  @Output() signup = new EventEmitter<string>();
  @Output() withdraw = new EventEmitter<string>();
}
