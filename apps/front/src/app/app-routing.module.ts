import { inject, NgModule } from '@angular/core';
import { CanActivateFn, Router, RouterModule, Routes } from '@angular/router';
import { BlankComponent } from './layouts/blank/blank.component';
import { FullComponent } from './layouts/full/full.component';
import { AuthenticationService } from '../services/authentication.service';
import { NotfoundComponent } from './pages/error/notfound.component';

const userLogged: CanActivateFn = () => {
  const isAuth = inject(AuthenticationService).checkAuthentication();
  if (isAuth) return true;
  return inject(Router).createUrlTree(['/login']);
};

const userLogoff: CanActivateFn = () => {
  const isAuth = inject(AuthenticationService).checkAuthentication();
  if (isAuth) return inject(Router).createUrlTree(['/dashboard']);
  return true;
};

const userAdmin: CanActivateFn = () => {
  const isAdmin = inject(AuthenticationService).checkAuthentication('ROLE_ADMIN');
  if (isAdmin) return true;
  return inject(Router).createUrlTree(['/dashboard']);
};

const routes: Routes = [
  {
    path: '',
    redirectTo: 'dashboard',
    pathMatch: 'full',
  },
  {
    path: '',
    component: FullComponent,
    canActivateChild: [userLogged],
    children: [
      {
        path: '',
        redirectTo: '/dashboard',
        pathMatch: 'full',
      },
      {
        path: '',
        loadChildren: () => import('./pages/admin/admin.module').then((m) => m.AdminModule),
        canActivateChild: [userAdmin],
      },
      {
        path: '',
        loadChildren: () => import('./pages/pages.module').then((m) => m.PagesModule),
      },
    ],
  },
  {
    path: '',
    component: BlankComponent,
    canActivateChild: [userLogoff],
    children: [
      {
        path: '',
        loadChildren: () =>
          import('./pages/authentication/authentication.module').then(
            (m) => m.AuthenticationModule,
          ),
      },
    ],
  },
  {
    path: '**',
    component: NotfoundComponent,
  },
];

@NgModule({
  imports: [RouterModule.forRoot(routes)],
  exports: [RouterModule],
})
export class AppRoutingModule {}
