import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { MatCardModule } from '@angular/material/card';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatInputModule } from '@angular/material/input';
import { MatFormFieldModule } from '@angular/material/form-field';
import { FeedbackMessageComponent } from '../../../components/atoms/feedback-message/feedback-message.component';
import { SpinnerOverlayComponent } from '../../../components/atoms/spinner-overlay/spinner-overlay.component';
import { AdminRepository } from '../../../../domain/interfaces/AdminRepository';
import { AdminCrudBase } from '../admin-crud.base';

@Component({
  selector: 'app-specialities-page',
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    MatCardModule,
    MatButtonModule,
    MatIconModule,
    MatInputModule,
    MatFormFieldModule,
    FeedbackMessageComponent,
    SpinnerOverlayComponent,
  ],
  templateUrl: './specialities-page.component.html',
  styleUrls: ['./specialities-page.component.scss'],
})
export class SpecialitiesPageComponent extends AdminCrudBase implements OnInit {
  specialities: any[] = [];
  editingSpec: any = null;
  formName = '';
  formAbbreviation = '';

  constructor(private adminRepo: AdminRepository) {
    super();
  }

  ngOnInit(): void {
    this.loadAll();
  }

  loadAll(): void {
    this.loading = true;
    this.adminRepo.listSpecialities().subscribe({
      next: (specs) => {
        this.specialities = specs;
        this.loading = false;
      },
      error: () => {
        this.loading = false;
      },
    });
  }

  openCreate(): void {
    this.editingSpec = null;
    this.formName = '';
    this.formAbbreviation = '';
    this.showForm = true;
  }

  openEdit(spec: any): void {
    this.editingSpec = spec;
    this.formName = spec.name;
    this.formAbbreviation = spec.abbreviation;
    this.showForm = true;
  }

  cancelForm(): void {
    this.showForm = false;
  }

  save(): void {
    this.saving = true;
    this.message = null;

    if (this.editingSpec) {
      this.adminRepo
        .updateSpeciality(this.editingSpec.id, {
          name: this.formName,
          abbreviation: this.formAbbreviation,
        })
        .subscribe({
          next: () => {
            this.onSuccess('Speciality updated.');
          },
          error: (err) => {
            this.onError(err);
          },
        });
    } else {
      this.adminRepo.createSpeciality(this.formName, this.formAbbreviation).subscribe({
        next: () => {
          this.onSuccess('Speciality created.');
        },
        error: (err) => {
          this.onError(err);
        },
      });
    }
  }

  deleteSpec(spec: any): void {
    this.saving = true;
    this.message = null;
    this.adminRepo.deleteSpeciality(spec.id).subscribe({
      next: () => {
        this.onSuccess('Speciality deleted.');
      },
      error: (err) => {
        this.onError(err);
      },
    });
  }
}
