import { Observable } from 'rxjs';

export interface RequirementDto {
  id: string;
  name: string;
}

export interface SpecialityDto {
  id: string;
  name: string;
  abbreviation: string;
}

export interface UnitComponentDto {
  id: string;
  component: { id: string; name: string };
  quantity: number;
}

export interface UnitDto {
  id: string;
  name: string;
  identifier: string;
  speciality: SpecialityDto | null;
  unitComponents: UnitComponentDto[];
}

export interface RoleDto {
  id: string;
  name: string;
  requirements: RequirementDto[];
}

export interface MemberDto {
  id: string;
  role: string;
  user: {
    id: string;
    name: string;
    surname: string;
    email: string;
    dateStart: string | null;
    requirements: RequirementDto[];
  };
}

export interface OrganizationDto {
  id: string;
  name: string;
  role: string;
}

export interface ProfileDto {
  id: string;
  name: string;
  surname: string;
  email: string;
  roles: string[];
  dateStart: string | null;
  stats: { totalServices: number; totalHours: number };
  organizations: OrganizationDto[];
  requirements: RequirementDto[];
}

export abstract class AdminRepository {
  // Units
  abstract listUnits(): Observable<UnitDto[]>;
  abstract registerUnit(
    organizationId: string,
    name: string,
    identifier: string,
    specialityId?: string,
  ): Observable<UnitDto>;
  abstract updateUnit(
    unitId: string,
    data: { name?: string; identifier?: string; speciality_id?: string },
  ): Observable<UnitDto>;
  abstract decommissionUnit(unitId: string): Observable<void>;
  abstract assignRoleToUnit(unitId: string, componentId: string, quantity: number): Observable<UnitComponentDto>;
  abstract unassignRoleFromUnit(unitId: string, unitComponentId: string): Observable<void>;

  // Components (Roles)
  abstract listRoles(): Observable<RoleDto[]>;
  abstract createRole(name: string, requirementIds: string[]): Observable<RoleDto>;
  abstract updateRole(componentId: string, data: { name?: string; requirement_ids?: string[] }): Observable<RoleDto>;
  abstract deleteRole(componentId: string): Observable<void>;

  // Requirements
  abstract listRequirements(): Observable<RequirementDto[]>;
  abstract createRequirement(name: string): Observable<RequirementDto>;
  abstract renameRequirement(requirementId: string, name: string): Observable<RequirementDto>;
  abstract deleteRequirement(requirementId: string): Observable<void>;

  // Specialities
  abstract listSpecialities(): Observable<SpecialityDto[]>;
  abstract createSpeciality(name: string, abbreviation: string): Observable<SpecialityDto>;
  abstract updateSpeciality(
    specialityId: string,
    data: { name?: string; abbreviation?: string },
  ): Observable<SpecialityDto>;
  abstract deleteSpeciality(specialityId: string): Observable<void>;

  // Members
  abstract listMembers(organizationId: string): Observable<MemberDto[]>;
  abstract inviteMember(
    organizationId: string,
    data: { email: string; name: string; surname: string; role: string; password?: string },
  ): Observable<MemberDto>;
  abstract removeMember(organizationId: string, userId: string): Observable<void>;
  abstract changeMemberRole(organizationId: string, userId: string, role: string): Observable<MemberDto>;

  // Profile
  abstract getProfile(): Observable<ProfileDto>;
  abstract updateProfile(data: { name?: string; surname?: string }): Observable<ProfileDto>;

  // User requirements
  abstract addUserRequirement(requirementId: string): Observable<void>;
  abstract removeUserRequirement(requirementId: string): Observable<void>;
}
