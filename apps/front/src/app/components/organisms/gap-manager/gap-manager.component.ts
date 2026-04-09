import { Component, Input, Output, EventEmitter } from '@angular/core';
import { CommonModule } from '@angular/common';
import { MatCardModule } from '@angular/material/card';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatSelectModule } from '@angular/material/select';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatChipsModule } from '@angular/material/chips';
import { FormsModule } from '@angular/forms';
import { Gap } from '../../../../domain/entities/Gap';

@Component({
  selector: 'app-gap-manager',
  standalone: true,
  imports: [
    CommonModule,
    MatCardModule,
    MatButtonModule,
    MatIconModule,
    MatSelectModule,
    MatFormFieldModule,
    MatChipsModule,
    FormsModule,
  ],
  templateUrl: './gap-manager.component.html',
  styleUrls: ['./gap-manager.component.scss'],
})
export class GapManagerComponent {
  @Input() gaps: Gap[] = [];
  @Input() availableUnitComponents: any[] = [];
  @Input() loading = false;
  @Output() addGap = new EventEmitter<string>();
  @Output() removeGap = new EventEmitter<string>();

  selectedUnitComponentId = '';

  onAdd(): void {
    if (!this.selectedUnitComponentId) return;
    this.addGap.emit(this.selectedUnitComponentId);
    this.selectedUnitComponentId = '';
  }

  onRemove(gapId: string): void {
    this.removeGap.emit(gapId);
  }

  canDelete(gap: Gap): boolean {
    return !gap.user;
  }
}
