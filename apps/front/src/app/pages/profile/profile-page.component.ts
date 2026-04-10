import { Component, DestroyRef, inject, OnInit } from '@angular/core';
import { takeUntilDestroyed } from '@angular/core/rxjs-interop';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { MatCardModule } from '@angular/material/card';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatInputModule } from '@angular/material/input';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatChipsModule } from '@angular/material/chips';
import { MatSelectModule } from '@angular/material/select';
import { FeedbackMessageComponent } from '../../components/atoms/feedback-message/feedback-message.component';
import { SpinnerOverlayComponent } from '../../components/atoms/spinner-overlay/spinner-overlay.component';
import { AdminRepository } from '../../../domain/interfaces/AdminRepository';

@Component({
  selector: 'app-profile-page',
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    MatCardModule,
    MatButtonModule,
    MatIconModule,
    MatInputModule,
    MatFormFieldModule,
    MatChipsModule,
    MatSelectModule,
    FeedbackMessageComponent,
    SpinnerOverlayComponent,
  ],
  templateUrl: './profile-page.component.html',
  styleUrls: ['./profile-page.component.scss'],
})
export class ProfilePageComponent implements OnInit {
  profile: any = null;
  allRequirements: any[] = [];
  loading = true;
  saving = false;
  message: { text: string; type: 'success' | 'error' } | null = null;

  editingProfile = false;
  formName = '';
  formSurname = '';

  selectedRequirementId = '';

  private destroyRef = inject(DestroyRef);

  constructor(private adminRepo: AdminRepository) {}

  ngOnInit(): void {
    this.loadProfile();
    this.adminRepo
      .listRequirements()
      .pipe(takeUntilDestroyed(this.destroyRef))
      .subscribe({
        next: (r) => {
          this.allRequirements = r;
        },
        error: () => {
          this.message = { text: 'Error al cargar los requisitos.', type: 'error' };
        },
      });
  }

  private loadProfile(): void {
    this.loading = true;
    this.adminRepo
      .getProfile()
      .pipe(takeUntilDestroyed(this.destroyRef))
      .subscribe({
        next: (profile) => {
          this.profile = profile;
          this.loading = false;
        },
        error: () => {
          this.loading = false;
          this.message = { text: 'Error al cargar el perfil.', type: 'error' };
        },
      });
  }

  startEdit(): void {
    this.formName = this.profile.name;
    this.formSurname = this.profile.surname;
    this.editingProfile = true;
  }

  cancelEdit(): void {
    this.editingProfile = false;
  }

  saveProfile(): void {
    this.saving = true;
    this.message = null;
    this.adminRepo
      .updateProfile({ name: this.formName, surname: this.formSurname })
      .pipe(takeUntilDestroyed(this.destroyRef))
      .subscribe({
        next: () => {
          this.saving = false;
          this.editingProfile = false;
          this.message = { text: 'Profile updated.', type: 'success' };
          this.loadProfile();
        },
        error: (err) => {
          this.saving = false;
          this.message = { text: err.error?.error || 'Error updating profile.', type: 'error' };
        },
      });
  }

  get availableRequirements(): any[] {
    const myIds = (this.profile?.requirements || []).map((r: any) => r.id);
    return this.allRequirements.filter((r) => !myIds.includes(r.id));
  }

  addRequirement(): void {
    if (!this.selectedRequirementId) return;
    this.saving = true;
    this.adminRepo
      .addUserRequirement(this.selectedRequirementId)
      .pipe(takeUntilDestroyed(this.destroyRef))
      .subscribe({
        next: () => {
          this.saving = false;
          this.selectedRequirementId = '';
          this.message = { text: 'Requirement added.', type: 'success' };
          this.loadProfile();
        },
        error: () => {
          this.saving = false;
          this.message = { text: 'Error adding requirement.', type: 'error' };
        },
      });
  }

  removeRequirement(reqId: string): void {
    this.saving = true;
    this.adminRepo
      .removeUserRequirement(reqId)
      .pipe(takeUntilDestroyed(this.destroyRef))
      .subscribe({
        next: () => {
          this.saving = false;
          this.message = { text: 'Requirement removed.', type: 'success' };
          this.loadProfile();
        },
        error: () => {
          this.saving = false;
          this.message = { text: 'Error removing requirement.', type: 'error' };
        },
      });
  }
}
