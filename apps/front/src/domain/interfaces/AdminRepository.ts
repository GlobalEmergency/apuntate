import { Observable } from 'rxjs';

export abstract class AdminRepository {
  // Units
  abstract listUnits(): Observable<any[]>;
  abstract registerUnit(name: string, identifier: string, specialityId?: string): Observable<any>;
  abstract updateUnit(unitId: string, data: { name?: string; identifier?: string; speciality_id?: string }): Observable<any>;
  abstract decommissionUnit(unitId: string): Observable<void>;
  abstract assignRoleToUnit(unitId: string, componentId: string, quantity: number): Observable<any>;
  abstract unassignRoleFromUnit(unitId: string, unitComponentId: string): Observable<void>;

  // Components (Roles)
  abstract listRoles(): Observable<any[]>;
  abstract createRole(name: string, requirementIds: string[]): Observable<any>;
  abstract updateRole(componentId: string, data: { name?: string; requirement_ids?: string[] }): Observable<any>;
  abstract deleteRole(componentId: string): Observable<void>;

  // Requirements
  abstract listRequirements(): Observable<any[]>;
  abstract createRequirement(name: string): Observable<any>;
  abstract renameRequirement(requirementId: string, name: string): Observable<any>;
  abstract deleteRequirement(requirementId: string): Observable<void>;

  // Specialities
  abstract listSpecialities(): Observable<any[]>;
  abstract createSpeciality(name: string, abbreviation: string): Observable<any>;
  abstract updateSpeciality(specialityId: string, data: { name?: string; abbreviation?: string }): Observable<any>;
  abstract deleteSpeciality(specialityId: string): Observable<void>;

  // Members
  abstract listMembers(organizationId: string): Observable<any[]>;
  abstract inviteMember(organizationId: string, data: { email: string; name: string; surname: string; role: string; password?: string }): Observable<any>;
  abstract removeMember(organizationId: string, userId: string): Observable<void>;
  abstract changeMemberRole(organizationId: string, userId: string, role: string): Observable<any>;

  // Profile
  abstract getProfile(): Observable<any>;
  abstract updateProfile(data: { name?: string; surname?: string }): Observable<any>;
}
