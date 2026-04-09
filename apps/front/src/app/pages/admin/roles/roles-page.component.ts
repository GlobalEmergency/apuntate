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
import { AdminCrudBase } from '../admin-crud.base';
import { forkJoin } from 'rxjs';

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
export class RolesPageComponent extends AdminCrudBase implements OnInit {
  roles: any[] = [];
  requirements: any[] = [];
  editingRole: any = null;
  formName = '';
  formRequirementIds: string[] = [];

  constructor(private adminRepo: AdminRepository) {
    super();
  }

  ngOnInit(): void {
    this.loadAll();
  }

  loadAll(): void {
    this.loading = true;
    forkJoin({
      roles: this.adminRepo.listRoles(),
      requirements: this.adminRepo.listRequirements(),
    }).subscribe({
      next: ({ roles, requirements }) => {
        this.roles = roles;
        this.requirements = requirements;
        this.loading = false;
      },
      error: () => {
        this.loading = false;
        this.message = { text: 'Error al cargar los datos.', type: 'error' };
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

}
