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
  selector: 'app-requirements-page',
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
  templateUrl: './requirements-page.component.html',
  styleUrls: ['./requirements-page.component.scss'],
})
export class RequirementsPageComponent extends AdminCrudBase implements OnInit {
  requirements: any[] = [];
  editingReq: any = null;
  formName = '';

  constructor(private adminRepo: AdminRepository) {
    super();
  }

  ngOnInit(): void {
    this.loadAll();
  }

  loadAll(): void {
    this.loading = true;
    this.adminRepo.listRequirements().subscribe({
      next: (reqs) => {
        this.requirements = reqs;
        this.loading = false;
      },
      error: () => {
        this.loading = false;
      },
    });
  }

  openCreate(): void {
    this.editingReq = null;
    this.formName = '';
    this.showForm = true;
  }

  openEdit(req: any): void {
    this.editingReq = req;
    this.formName = req.name;
    this.showForm = true;
  }

  cancelForm(): void {
    this.showForm = false;
  }

  save(): void {
    this.saving = true;
    this.message = null;

    if (this.editingReq) {
      this.adminRepo.renameRequirement(this.editingReq.id, this.formName).subscribe({
        next: () => {
          this.onSuccess('Requirement renamed.');
        },
        error: (err) => {
          this.onError(err);
        },
      });
    } else {
      this.adminRepo.createRequirement(this.formName).subscribe({
        next: () => {
          this.onSuccess('Requirement created.');
        },
        error: (err) => {
          this.onError(err);
        },
      });
    }
  }

  deleteReq(req: any): void {
    this.saving = true;
    this.message = null;
    this.adminRepo.deleteRequirement(req.id).subscribe({
      next: () => {
        this.onSuccess('Requirement deleted.');
      },
      error: (err) => {
        this.onError(err);
      },
    });
  }

}
