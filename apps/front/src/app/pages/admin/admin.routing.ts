import { Routes } from '@angular/router';
import { ServiceAddComponent } from './ServiceAdd/serviceAdd.component';
import { ServiceEditComponent } from './service-edit/service-edit.component';
import { UnitsPageComponent } from './units/units-page.component';
import { RolesPageComponent } from './roles/roles-page.component';
import { RequirementsPageComponent } from './requirements/requirements-page.component';
import { SpecialitiesPageComponent } from './specialities/specialities-page.component';
import { MembersPageComponent } from './members/members-page.component';

export const AdminRouting: Routes = [
  {
    path: '',
    children: [
      { path: 'service/add', component: ServiceAddComponent },
      { path: 'service/:id/edit', component: ServiceEditComponent },
      { path: 'admin/units', component: UnitsPageComponent },
      { path: 'admin/components', component: RolesPageComponent },
      { path: 'admin/requirements', component: RequirementsPageComponent },
      { path: 'admin/specialities', component: SpecialitiesPageComponent },
      { path: 'admin/members', component: MembersPageComponent },
    ],
  },
];
