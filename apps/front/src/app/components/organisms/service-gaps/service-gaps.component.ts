import { Component, Input, Output, EventEmitter } from '@angular/core';
import { CommonModule } from '@angular/common';
import { MatCardModule } from '@angular/material/card';
import { GapItemComponent } from '../../molecules/gap-item/gap-item.component';
import { SummaryRowComponent } from '../../atoms/summary-row/summary-row.component';
import { Gap } from '../../../../domain/entities/Gap';

@Component({
  selector: 'app-service-gaps',
  standalone: true,
  imports: [CommonModule, MatCardModule, GapItemComponent, SummaryRowComponent],
  templateUrl: './service-gaps.component.html',
  styleUrls: ['./service-gaps.component.scss'],
})
export class ServiceGapsComponent {
  @Input() gaps: Gap[] = [];
  @Input() signupLoading: string | null = null;
  @Input() currentUserId = '';
  @Output() signup = new EventEmitter<string>();
  @Output() withdraw = new EventEmitter<string>();

  get filledCount(): number {
    return this.gaps.filter((g) => g.user !== null && g.user !== undefined).length;
  }

  get availableCount(): number {
    return this.gaps.length - this.filledCount;
  }

  isCurrentUser(gap: Gap): boolean {
    return gap.user?.id === this.currentUserId;
  }
}
