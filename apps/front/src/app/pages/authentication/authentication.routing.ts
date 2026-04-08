import { Routes } from '@angular/router';

import { LoginPage } from './login/login.component';
import { RegisterComponent } from './register/register.component';

export const AuthenticationRoutes: Routes = [
  {
    path: '',
    children: [
      {
        path: 'login',
        component: LoginPage,
      },
      {
        path: 'register',
        component: RegisterComponent,
      },
    ],
  },
];
