import { Component, Inject, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { ActivatedRoute, Router } from '@angular/router';
import { AuthenticationService } from '../../../../services/authentication.service';

@Component({
  standalone: false,
  selector: 'app-reset-password',
  templateUrl: './reset-password.component.html',
})
export class ResetPasswordComponent implements OnInit {
  loading = false;
  message: { text: string; type: 'success' | 'error' } | null = null;
  token = '';

  form: FormGroup = new FormGroup({
    password: this.fb.control('', [Validators.required, Validators.minLength(6)]),
    confirmPassword: this.fb.control('', [Validators.required]),
  });

  constructor(
    private fb: FormBuilder,
    private route: ActivatedRoute,
    private router: Router,
    @Inject(AuthenticationService) private authService: AuthenticationService,
  ) {}

  ngOnInit() {
    this.route.queryParamMap.subscribe((params) => {
      this.token = params.get('token') || '';
      if (!this.token) {
        this.message = { text: 'Enlace no válido o caducado.', type: 'error' };
      }
    });
  }

  submit() {
    if (!this.form.valid || !this.token) {
      return;
    }

    if (this.form.value.password !== this.form.value.confirmPassword) {
      this.message = { text: 'Las contraseñas no coinciden.', type: 'error' };
      return;
    }

    this.loading = true;
    this.message = null;

    this.authService.confirmPasswordReset(this.token, this.form.value.password).subscribe({
      next: () => {
        this.loading = false;
        this.message = {
          text: 'Contraseña establecida correctamente. Redirigiendo al inicio de sesión...',
          type: 'success',
        };
        setTimeout(() => this.router.navigateByUrl('/login', { replaceUrl: true }), 2000);
      },
      error: (error) => {
        this.loading = false;
        if (error.status === 400) {
          this.message = { text: error.error?.error || 'Enlace no válido o caducado.', type: 'error' };
        } else {
          this.message = { text: 'Error al establecer la contraseña. Inténtalo de nuevo.', type: 'error' };
        }
      },
    });
  }

  get password() {
    return this.form.get('password');
  }

  get confirmPassword() {
    return this.form.get('confirmPassword');
  }
}
