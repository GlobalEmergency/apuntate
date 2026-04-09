import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../environments/environment';
import {
  AdminRepository,
  MemberDto,
  ProfileDto,
  RequirementDto,
  RoleDto,
  SpecialityDto,
  UnitComponentDto,
  UnitDto,
} from '../../domain/interfaces/AdminRepository';

@Injectable()
export class AdminHttpRepository extends AdminRepository {
  private readonly url = environment.api_url;

  constructor(private http: HttpClient) {
    super();
  }

  // Units
  listUnits(): Observable<UnitDto[]> {
    return this.http.get<UnitDto[]>(`${this.url}/units`);
  }

  registerUnit(name: string, identifier: string, specialityId?: string): Observable<UnitDto> {
    return this.http.post<UnitDto>(`${this.url}/units`, {
      name,
      identifier,
      speciality_id: specialityId || null,
    });
  }

  updateUnit(unitId: string, data: { name?: string; identifier?: string; speciality_id?: string }): Observable<UnitDto> {
    return this.http.put<UnitDto>(`${this.url}/units/${unitId}`, data);
  }

  decommissionUnit(unitId: string): Observable<void> {
    return this.http.delete<void>(`${this.url}/units/${unitId}`);
  }

  assignRoleToUnit(unitId: string, componentId: string, quantity: number): Observable<UnitComponentDto> {
    return this.http.post<UnitComponentDto>(`${this.url}/units/${unitId}/roles`, { component_id: componentId, quantity });
  }

  unassignRoleFromUnit(unitId: string, unitComponentId: string): Observable<void> {
    return this.http.delete<void>(`${this.url}/units/${unitId}/roles/${unitComponentId}`);
  }

  // Roles
  listRoles(): Observable<RoleDto[]> {
    return this.http.get<RoleDto[]>(`${this.url}/components`);
  }

  createRole(name: string, requirementIds: string[]): Observable<RoleDto> {
    return this.http.post<RoleDto>(`${this.url}/components`, { name, requirement_ids: requirementIds });
  }

  updateRole(componentId: string, data: { name?: string; requirement_ids?: string[] }): Observable<RoleDto> {
    return this.http.put<RoleDto>(`${this.url}/components/${componentId}`, data);
  }

  deleteRole(componentId: string): Observable<void> {
    return this.http.delete<void>(`${this.url}/components/${componentId}`);
  }

  // Requirements
  listRequirements(): Observable<RequirementDto[]> {
    return this.http.get<RequirementDto[]>(`${this.url}/requirements`);
  }

  createRequirement(name: string): Observable<RequirementDto> {
    return this.http.post<RequirementDto>(`${this.url}/requirements`, { name });
  }

  renameRequirement(requirementId: string, name: string): Observable<RequirementDto> {
    return this.http.put<RequirementDto>(`${this.url}/requirements/${requirementId}`, { name });
  }

  deleteRequirement(requirementId: string): Observable<void> {
    return this.http.delete<void>(`${this.url}/requirements/${requirementId}`);
  }

  // Specialities
  listSpecialities(): Observable<SpecialityDto[]> {
    return this.http.get<SpecialityDto[]>(`${this.url}/specialities`);
  }

  createSpeciality(name: string, abbreviation: string): Observable<SpecialityDto> {
    return this.http.post<SpecialityDto>(`${this.url}/specialities`, { name, abbreviation });
  }

  updateSpeciality(specialityId: string, data: { name?: string; abbreviation?: string }): Observable<SpecialityDto> {
    return this.http.put<SpecialityDto>(`${this.url}/specialities/${specialityId}`, data);
  }

  deleteSpeciality(specialityId: string): Observable<void> {
    return this.http.delete<void>(`${this.url}/specialities/${specialityId}`);
  }

  // Members
  listMembers(organizationId: string): Observable<MemberDto[]> {
    return this.http.get<MemberDto[]>(`${this.url}/organizations/${organizationId}/members`);
  }

  inviteMember(
    organizationId: string,
    data: { email: string; name: string; surname: string; role: string; password?: string },
  ): Observable<MemberDto> {
    return this.http.post<MemberDto>(`${this.url}/organizations/${organizationId}/members`, data);
  }

  removeMember(organizationId: string, userId: string): Observable<void> {
    return this.http.delete<void>(`${this.url}/organizations/${organizationId}/members/${userId}`);
  }

  changeMemberRole(organizationId: string, userId: string, role: string): Observable<MemberDto> {
    return this.http.put<MemberDto>(`${this.url}/organizations/${organizationId}/members/${userId}/role`, { role });
  }

  // Profile
  getProfile(): Observable<ProfileDto> {
    return this.http.get<ProfileDto>(`${this.url}/profile`);
  }

  updateProfile(data: { name?: string; surname?: string }): Observable<ProfileDto> {
    return this.http.put<ProfileDto>(`${this.url}/profile`, data);
  }

  // User requirements
  addUserRequirement(requirementId: string): Observable<void> {
    return this.http.post<void>(`${this.url}/requirements/user/${requirementId}`, {});
  }

  removeUserRequirement(requirementId: string): Observable<void> {
    return this.http.delete<void>(`${this.url}/requirements/user/${requirementId}`);
  }
}
