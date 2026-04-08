import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { Router } from '@angular/router';
import { Inject } from '@angular/core';
import { AuthenticationService } from '../../../../services/authentication.service';
import { AlertService } from 'src/services/alert.service';

@Component({
  selector: 'app-login',
  templateUrl: './login.component.html',
})
export class LoginPage implements OnInit {
  credentials: FormGroup = new FormGroup({
    email: this.fb.control('', [Validators.required, Validators.email]),
    password: this.fb.control('', [Validators.required, Validators.minLength(3)]),
  });

  constructor(
    private fb: FormBuilder,
    @Inject(AuthenticationService) private authService: AuthenticationService,
    private router: Router,
    private alertService: AlertService
  ) {
    console.log('LoginPage.constructor()');
  }

  ngOnInit(): void {
    // throw new Error('Method not implemented.');
  }

  async login() {
    // const loading = await this.loadingController.create();
    // await loading.present();

    const loginData = { username: this.credentials.value.email, password: this.credentials.value.password };
    this.authService.login(loginData).subscribe(
      async (res: any) => {
        // await loading.dismiss();
        this.router.navigateByUrl('/', { replaceUrl: true });
      },
      async (res: any) => {
        // await loading.dismiss();
        // const alert = await this.alertController.create({
        //   header: 'Login failed',
        //   message: res.error.error,
        //   buttons: ['OK'],
        // });

        // await alert.present();
        this.alertService.openSnackBar('Login failed.');
        console.warn('Login failed', res.error.error)
      }
    );
  }

  // Easy access for form fields
  get email() {
    return this.credentials.get('email');
  }

  get password() {
    return this.credentials.get('password');
  }
}
