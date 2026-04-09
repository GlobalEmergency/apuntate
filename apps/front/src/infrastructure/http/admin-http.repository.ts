import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../environments/environment';
import { AdminRepository } from '../../domain/interfaces/AdminRepository';

@Injectable()
export class AdminHttpRepository extends AdminRepository {
  private readonly url = environment.api_url;

  constructor(private http: HttpClient) {
    super();
  }

  // Units
  listUnits(): Observable<any[]> {
    return this.http.get<any[]>(`${this.url}/units`);
  }

  registerUnit(name: string, identifier: string, specialityId?: string): Observable<any> {
    return this.http.post(`${this.url}/units`, {
      name,
      identifier,
      speciality_id: specialityId || null,
    });
  }

  updateUnit(unitId: string, data: { name?: string; identifier?: string; speciality_id?: string }): Observable<any> {
    return this.http.put(`${this.url}/units/${unitId}`, data);
  }

  decommissionUnit(unitId: string): Observable<void> {
    return this.http.delete<void>(`${this.url}/units/${unitId}`);
  }

  assignRoleToUnit(unitId: string, componentId: string, quantity: number): Observable<any> {
    return this.http.post(`${this.url}/units/${unitId}/roles`, { component_id: componentId, quantity });
  }

  unassignRoleFromUnit(unitId: string, unitComponentId: string): Observable<void> {
    return this.http.delete<void>(`${this.url}/units/${unitId}/roles/${unitComponentId}`);
  }

  // Roles
  listRoles(): Observable<any[]> {
    return this.http.get<any[]>(`${this.url}/components`);
  }

  createRole(name: string, requirementIds: string[]): Observable<any> {
    return this.http.post(`${this.url}/components`, { name, requirement_ids: requirementIds });
  }

  updateRole(componentId: string, data: { name?: string; requirement_ids?: string[] }): Observable<any> {
    return this.http.put(`${this.url}/components/${componentId}`, data);
  }

  deleteRole(componentId: string): Observable<void> {
    return this.http.delete<void>(`${this.url}/components/${componentId}`);
  }

  // Requirements
  listRequirements(): Observable<any[]> {
    return this.http.get<any[]>(`${this.url}/requirements`);
  }

  createRequirement(name: string): Observable<any> {
    return this.http.post(`${this.url}/requirements`, { name });
  }

  renameRequirement(requirementId: string, name: string): Observable<any> {
    return this.http.put(`${this.url}/requirements/${requirementId}`, { name });
  }

  deleteRequirement(requirementId: string): Observable<void> {
    return this.http.delete<void>(`${this.url}/requirements/${requirementId}`);
  }

  // Specialities
  listSpecialities(): Observable<any[]> {
    return this.http.get<any[]>(`${this.url}/specialities`);
  }

  createSpeciality(name: string, abbreviation: string): Observable<any> {
    return this.http.post(`${this.url}/specialities`, { name, abbreviation });
  }

  updateSpeciality(specialityId: string, data: { name?: string; abbreviation?: string }): Observable<any> {
    return this.http.put(`${this.url}/specialities/${specialityId}`, data);
  }

  deleteSpeciality(specialityId: string): Observable<void> {
    return this.http.delete<void>(`${this.url}/specialities/${specialityId}`);
  }

  // Members
  listMembers(organizationId: string): Observable<any[]> {
    return this.http.get<any[]>(`${this.url}/organizations/${organizationId}/members`);
  }

  inviteMember(
    organizationId: string,
    data: { email: string; name: string; surname: string; role: string; password?: string },
  ): Observable<any> {
    return this.http.post(`${this.url}/organizations/${organizationId}/members`, data);
  }

  removeMember(organizationId: string, userId: string): Observable<void> {
    return this.http.delete<void>(`${this.url}/organizations/${organizationId}/members/${userId}`);
  }

  changeMemberRole(organizationId: string, userId: string, role: string): Observable<any> {
    return this.http.put(`${this.url}/organizations/${organizationId}/members/${userId}/role`, { role });
  }

  // Profile
  getProfile(): Observable<any> {
    return this.http.get(`${this.url}/profile`);
  }

  updateProfile(data: { name?: string; surname?: string }): Observable<any> {
    return this.http.put(`${this.url}/profile`, data);
  }
}
