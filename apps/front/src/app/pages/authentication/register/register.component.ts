import { Component, Inject } from '@angular/core';
import { FormGroup, FormControl, Validators } from '@angular/forms';
import { Router } from '@angular/router';
import { AuthenticationService } from '../../../../services/authentication.service';
import { AlertService } from '../../../../services/alert.service';

@Component({
  selector: 'app-register',
  templateUrl: './register.component.html',
})
export class RegisterComponent {
  loading = false;

  constructor(
    private router: Router,
    @Inject(AuthenticationService) private authService: AuthenticationService,
    private alertService: AlertService,
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

    this.authService.register(this.form.value).subscribe({
      next: () => {
        this.loading = false;
        this.alertService.openSnackBar('Cuenta creada correctamente. Inicia sesión.', 'OK');
        this.router.navigate(['/login']);
      },
      error: (error) => {
        this.loading = false;
        if (error.status === 409) {
          this.alertService.openSnackBar('Ya existe una cuenta con este email.', 'Cerrar');
        } else if (error.status === 400) {
          this.alertService.openSnackBar('Revisa los campos del formulario.', 'Cerrar');
        } else {
          this.alertService.openSnackBar('Error al registrar. Inténtalo de nuevo.', 'Cerrar');
        }
      },
    });
  }
}
