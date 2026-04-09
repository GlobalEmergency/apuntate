import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { MatCardModule } from '@angular/material/card';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatInputModule } from '@angular/material/input';
import { MatSelectModule } from '@angular/material/select';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatChipsModule } from '@angular/material/chips';
import { FeedbackMessageComponent } from '../../../components/atoms/feedback-message/feedback-message.component';
import { SpinnerOverlayComponent } from '../../../components/atoms/spinner-overlay/spinner-overlay.component';
import { AdminRepository } from '../../../../domain/interfaces/AdminRepository';

@Component({
  selector: 'app-units-page',
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    MatCardModule,
    MatButtonModule,
    MatIconModule,
    MatInputModule,
    MatSelectModule,
    MatFormFieldModule,
    MatChipsModule,
    FeedbackMessageComponent,
    SpinnerOverlayComponent,
  ],
  templateUrl: './units-page.component.html',
  styleUrls: ['./units-page.component.scss'],
})
export class UnitsPageComponent implements OnInit {
  units: any[] = [];
  specialities: any[] = [];
  roles: any[] = [];
  loading = true;
  saving = false;
  message: { text: string; type: 'success' | 'error' } | null = null;

  showForm = false;
  editingUnit: any = null;
  formName = '';
  formIdentifier = '';
  formSpecialityId = '';

  expandedUnitId: string | null = null;
  newRoleComponentId = '';
  newRoleQuantity = 1;

  constructor(private adminRepo: AdminRepository) {}

  ngOnInit(): void {
    this.loadAll();
  }

  private loadAll(): void {
    this.loading = true;
    this.adminRepo.listUnits().subscribe({
      next: (units) => {
        this.units = units;
        this.loading = false;
      },
      error: () => {
        this.loading = false;
      },
    });
    this.adminRepo.listSpecialities().subscribe({
      next: (s) => {
        this.specialities = s;
      },
    });
    this.adminRepo.listRoles().subscribe({
      next: (r) => {
        this.roles = r;
      },
    });
  }

  openCreate(): void {
    this.editingUnit = null;
    this.formName = '';
    this.formIdentifier = '';
    this.formSpecialityId = '';
    this.showForm = true;
  }

  openEdit(unit: any): void {
    this.editingUnit = unit;
    this.formName = unit.name;
    this.formIdentifier = unit.identifier;
    this.formSpecialityId = unit.speciality?.id || '';
    this.showForm = true;
  }

  cancelForm(): void {
    this.showForm = false;
  }

  save(): void {
    this.saving = true;
    this.message = null;

    if (this.editingUnit) {
      this.adminRepo
        .updateUnit(this.editingUnit.id, {
          name: this.formName,
          identifier: this.formIdentifier,
          speciality_id: this.formSpecialityId || undefined,
        })
        .subscribe({
          next: () => {
            this.onSuccess('Unit updated.');
          },
          error: (err) => {
            this.onError(err);
          },
        });
    } else {
      this.adminRepo.registerUnit(this.formName, this.formIdentifier, this.formSpecialityId || undefined).subscribe({
        next: () => {
          this.onSuccess('Unit registered.');
        },
        error: (err) => {
          this.onError(err);
        },
      });
    }
  }

  decommission(unit: any): void {
    this.saving = true;
    this.message = null;
    this.adminRepo.decommissionUnit(unit.id).subscribe({
      next: () => {
        this.onSuccess('Unit decommissioned.');
      },
      error: (err) => {
        this.onError(err);
      },
    });
  }

  toggleExpand(unitId: string): void {
    this.expandedUnitId = this.expandedUnitId === unitId ? null : unitId;
  }

  assignRole(unitId: string): void {
    if (!this.newRoleComponentId) return;
    this.saving = true;
    this.adminRepo.assignRoleToUnit(unitId, this.newRoleComponentId, this.newRoleQuantity).subscribe({
      next: () => {
        this.newRoleComponentId = '';
        this.newRoleQuantity = 1;
        this.onSuccess('Role assigned.');
      },
      error: (err) => {
        this.onError(err);
      },
    });
  }

  unassignRole(unitId: string, ucId: string): void {
    this.saving = true;
    this.adminRepo.unassignRoleFromUnit(unitId, ucId).subscribe({
      next: () => {
        this.onSuccess('Role unassigned.');
      },
      error: (err) => {
        this.onError(err);
      },
    });
  }

  private onSuccess(msg: string): void {
    this.saving = false;
    this.showForm = false;
    this.message = { text: msg, type: 'success' };
    this.loadAll();
  }

  private onError(err: any): void {
    this.saving = false;
    this.message = { text: err.error?.error || 'An error occurred.', type: 'error' };
  }
}
