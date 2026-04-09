import { Routes } from '@angular/router';
import { CalendarComponent } from './calendar/calendar.component';
import { DashboardComponent } from './dashboard/dashboard.component';
import { ServiceComponent } from './service/service.component';
import { ProfilePageComponent } from './profile/profile-page.component';

export const PagesRoutes: Routes = [
  {
    path: 'dashboard',
    component: DashboardComponent,
    data: { title: 'Dashboard' },
  },
  {
    path: 'calendar',
    component: CalendarComponent,
    data: { title: 'Calendar' },
  },
  {
    path: 'service/:id',
    component: ServiceComponent,
    data: { title: 'Service' },
  },
  {
    path: 'profile',
    component: ProfilePageComponent,
    data: { title: 'Profile' },
  },
];
