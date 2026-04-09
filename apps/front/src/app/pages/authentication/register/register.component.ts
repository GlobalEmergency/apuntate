import { Component, Inject } from '@angular/core';
import { FormGroup, FormControl, Validators } from '@angular/forms';
import { Router } from '@angular/router';
import { AuthenticationService } from '../../../../services/authentication.service';

@Component({
  standalone: false,
    selector: 'app-register',
    templateUrl: './register.component.html',
    
})
export class RegisterComponent {
  loading = false;
  message: { text: string; type: 'success' | 'error' } | null = null;

  constructor(
    private router: Router,
    @Inject(AuthenticationService) private authService: AuthenticationService,
  ) {}

  form = new FormGroup({
    org: new FormControl('', [Validators.required]),
    uname: new FormControl('', [Validators.required]),
    email: new FormControl('', [Validators.required, Validators.email]),
    password: new FormControl('', [Validators.required, Validators.minLength(6)]),
  });

  get f() {
    return this.form.controls;
  }

  submit() {
    if (!this.form.valid) {
      return;
    }

    this.loading = true;
    this.message = null;

    this.authService.register(this.form.value).subscribe({
      next: () => {
        this.loading = false;
        this.message = { text: 'Cuenta creada correctamente. Redirigiendo al login...', type: 'success' };
        setTimeout(() => this.router.navigate(['/login']), 2000);
      },
      error: (error) => {
        this.loading = false;
        if (error.status === 409) {
          this.message = { text: 'Ya existe una cuenta con este email.', type: 'error' };
        } else if (error.status === 400) {
          this.message = { text: 'Revisa los campos del formulario.', type: 'error' };
        } else {
          this.message = { text: 'Error al registrar. Inténtalo de nuevo.', type: 'error' };
        }
      },
    });
  }
}
