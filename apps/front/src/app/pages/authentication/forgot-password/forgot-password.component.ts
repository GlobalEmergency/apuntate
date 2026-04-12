import { Component, Inject } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { AuthenticationService } from '../../../../services/authentication.service';

@Component({
  standalone: false,
  selector: 'app-forgot-password',
  templateUrl: './forgot-password.component.html',
})
export class ForgotPasswordComponent {
  loading = false;
  message: { text: string; type: 'success' | 'error' } | null = null;

  form: FormGroup = new FormGroup({
    email: this.fb.control('', [Validators.required, Validators.email]),
  });

  constructor(
    private fb: FormBuilder,
    @Inject(AuthenticationService) private authService: AuthenticationService,
  ) {}

  submit() {
    if (!this.form.valid) {
      return;
    }

    this.loading = true;
    this.message = null;

    this.authService.requestPasswordReset(this.form.value.email).subscribe({
      next: () => {
        this.loading = false;
        this.message = {
          text: 'Si el email existe, recibirás un enlace para restablecer tu contraseña.',
          type: 'success',
        };
      },
      error: () => {
        this.loading = false;
        this.message = {
          text: 'Si el email existe, recibirás un enlace para restablecer tu contraseña.',
          type: 'success',
        };
      },
    });
  }

  get email() {
    return this.form.get('email');
  }
}
