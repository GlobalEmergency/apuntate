import { Component } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { Router } from '@angular/router';
import { Inject } from '@angular/core';
import { AuthenticationService } from '../../../../services/authentication.service';

@Component({
    selector: 'app-login',
    templateUrl: './login.component.html',
    standalone: false
})
export class LoginPage {
  loading = false;
  message: { text: string; type: 'success' | 'error' } | null = null;

  credentials: FormGroup = new FormGroup({
    email: this.fb.control('', [Validators.required, Validators.email]),
    password: this.fb.control('', [Validators.required, Validators.minLength(3)]),
  });

  constructor(
    private fb: FormBuilder,
    @Inject(AuthenticationService) private authService: AuthenticationService,
    private router: Router,
  ) {}

  login() {
    if (!this.credentials.valid) {
      return;
    }

    this.loading = true;
    this.message = null;

    const loginData = { username: this.credentials.value.email, password: this.credentials.value.password };
    this.authService.login(loginData).subscribe({
      next: () => {
        this.loading = false;
        this.router.navigateByUrl('/', { replaceUrl: true });
      },
      error: (error) => {
        this.loading = false;
        if (error.status === 401) {
          this.message = { text: 'Email o contraseña incorrectos.', type: 'error' };
        } else {
          this.message = { text: 'Error al iniciar sesión. Inténtalo de nuevo.', type: 'error' };
        }
      },
    });
  }

  get email() {
    return this.credentials.get('email');
  }

  get password() {
    return this.credentials.get('password');
  }
}
