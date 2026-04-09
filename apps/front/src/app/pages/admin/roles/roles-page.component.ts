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
  selector: 'app-roles-page',
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
  templateUrl: './roles-page.component.html',
  styleUrls: ['./roles-page.component.scss'],
})
export class RolesPageComponent implements OnInit {
  roles: any[] = [];
  requirements: any[] = [];
  loading = true;
  saving = false;
  message: { text: string; type: 'success' | 'error' } | null = null;

  showForm = false;
  editingRole: any = null;
  formName = '';
  formRequirementIds: string[] = [];

  constructor(private adminRepo: AdminRepository) {}

  ngOnInit(): void {
    this.loadAll();
  }

  private loadAll(): void {
    this.loading = true;
    this.adminRepo.listRoles().subscribe({
      next: (roles) => {
        this.roles = roles;
        this.loading = false;
      },
      error: () => {
        this.loading = false;
      },
    });
    this.adminRepo.listRequirements().subscribe({
      next: (r) => {
        this.requirements = r;
      },
    });
  }

  openCreate(): void {
    this.editingRole = null;
    this.formName = '';
    this.formRequirementIds = [];
    this.showForm = true;
  }

  openEdit(role: any): void {
    this.editingRole = role;
    this.formName = role.name;
    this.formRequirementIds = (role.requirements || []).map((r: any) => r.id);
    this.showForm = true;
  }

  cancelForm(): void {
    this.showForm = false;
  }

  save(): void {
    this.saving = true;
    this.message = null;

    if (this.editingRole) {
      this.adminRepo
        .updateRole(this.editingRole.id, {
          name: this.formName,
          requirement_ids: this.formRequirementIds,
        })
        .subscribe({
          next: () => {
            this.onSuccess('Role updated.');
          },
          error: (err) => {
            this.onError(err);
          },
        });
    } else {
      this.adminRepo.createRole(this.formName, this.formRequirementIds).subscribe({
        next: () => {
          this.onSuccess('Role created.');
        },
        error: (err) => {
          this.onError(err);
        },
      });
    }
  }

  deleteRole(role: any): void {
    this.saving = true;
    this.message = null;
    this.adminRepo.deleteRole(role.id).subscribe({
      next: () => {
        this.onSuccess('Role deleted.');
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
