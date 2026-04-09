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
  selector: 'app-members-page',
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
  templateUrl: './members-page.component.html',
  styleUrls: ['./members-page.component.scss'],
})
export class MembersPageComponent implements OnInit {
  members: any[] = [];
  organizations: any[] = [];
  selectedOrgId = '';
  loading = true;
  saving = false;
  message: { text: string; type: 'success' | 'error' } | null = null;

  showInviteForm = false;
  inviteEmail = '';
  inviteName = '';
  inviteSurname = '';
  inviteRole = 'member';
  invitePassword = '';

  roles = [
    { value: 'admin', label: 'Admin' },
    { value: 'manager', label: 'Manager' },
    { value: 'member', label: 'Member' },
  ];

  constructor(private adminRepo: AdminRepository) {}

  ngOnInit(): void {
    this.loadProfile();
  }

  private loadProfile(): void {
    this.adminRepo.getProfile().subscribe({
      next: (profile) => {
        this.organizations = profile.organizations || [];
        if (this.organizations.length > 0) {
          this.selectedOrgId = this.organizations[0].id;
          this.loadMembers();
        } else {
          this.loading = false;
        }
      },
      error: () => {
        this.loading = false;
        this.message = { text: 'Error al cargar las organizaciones.', type: 'error' };
      },
    });
  }

  onOrgChange(): void {
    this.loadMembers();
  }

  private loadMembers(): void {
    if (!this.selectedOrgId) return;
    this.loading = true;
    this.adminRepo.listMembers(this.selectedOrgId).subscribe({
      next: (members) => {
        this.members = members;
        this.loading = false;
      },
      error: () => {
        this.loading = false;
        this.message = { text: 'Error al cargar los miembros.', type: 'error' };
      },
    });
  }

  openInvite(): void {
    this.inviteEmail = '';
    this.inviteName = '';
    this.inviteSurname = '';
    this.inviteRole = 'member';
    this.invitePassword = '';
    this.showInviteForm = true;
  }

  cancelInvite(): void {
    this.showInviteForm = false;
  }

  invite(): void {
    this.saving = true;
    this.message = null;
    this.adminRepo
      .inviteMember(this.selectedOrgId, {
        email: this.inviteEmail,
        name: this.inviteName,
        surname: this.inviteSurname,
        role: this.inviteRole,
        password: this.invitePassword || undefined,
      })
      .subscribe({
        next: () => {
          this.saving = false;
          this.showInviteForm = false;
          this.message = { text: 'Member invited successfully.', type: 'success' };
          this.loadMembers();
        },
        error: (err) => {
          this.saving = false;
          this.message = { text: err.error?.error || 'Error inviting member.', type: 'error' };
        },
      });
  }

  changeRole(userId: string, newRole: string): void {
    this.saving = true;
    this.message = null;
    this.adminRepo.changeMemberRole(this.selectedOrgId, userId, newRole).subscribe({
      next: () => {
        this.saving = false;
        this.message = { text: 'Role updated.', type: 'success' };
        this.loadMembers();
      },
      error: (err) => {
        this.saving = false;
        this.message = { text: err.error?.error || 'Error changing role.', type: 'error' };
      },
    });
  }

  removeMember(userId: string, name: string): void {
    this.saving = true;
    this.message = null;
    this.adminRepo.removeMember(this.selectedOrgId, userId).subscribe({
      next: () => {
        this.saving = false;
        this.message = { text: `${name} removed from organization.`, type: 'success' };
        this.loadMembers();
      },
      error: (err) => {
        this.saving = false;
        this.message = { text: err.error?.error || 'Error removing member.', type: 'error' };
      },
    });
  }
}
