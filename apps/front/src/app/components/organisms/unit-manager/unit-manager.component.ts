import { Component, Input, Output, EventEmitter } from '@angular/core';
import { CommonModule } from '@angular/common';
import { MatCardModule } from '@angular/material/card';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatSelectModule } from '@angular/material/select';
import { MatFormFieldModule } from '@angular/material/form-field';
import { FormsModule } from '@angular/forms';

@Component({
  selector: 'app-unit-manager',
  standalone: true,
  imports: [CommonModule, MatCardModule, MatButtonModule, MatIconModule, MatSelectModule, MatFormFieldModule, FormsModule],
  templateUrl: './unit-manager.component.html',
  styleUrls: ['./unit-manager.component.scss'],
})
export class UnitManagerComponent {
  @Input() units: any[] = [];
  @Input() availableUnits: any[] = [];
  @Input() loading = false;
  @Output() addUnit = new EventEmitter<string>();
  @Output() removeUnit = new EventEmitter<string>();

  selectedUnitId = '';

  get unassignedUnits(): any[] {
    const assignedIds = this.units.map((u: any) => u.id?.toString());
    return this.availableUnits.filter((u: any) => !assignedIds.includes(u.id?.toString()));
  }

  onAdd(): void {
    if (!this.selectedUnitId) return;
    this.addUnit.emit(this.selectedUnitId);
    this.selectedUnitId = '';
  }

  onRemove(unitId: string): void {
    this.removeUnit.emit(unitId);
  }
}
