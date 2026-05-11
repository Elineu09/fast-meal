import { Component, OnInit } from '@angular/core';
import { FormControl, FormGroup, Validators } from '@angular/forms';
import { ActivatedRoute, Router } from '@angular/router';
import { finalize } from 'rxjs';

import { LoginRequest, UserRole } from '../../models/auth.model';
import { AuthService } from '../../services/auth.service';
import { extractApiErrorMessage } from '../../shared/helpers/http-error.helper';

type LoginForm = FormGroup<{
  email: FormControl<string>;
  password: FormControl<string>;
}>;

@Component({
  selector: 'app-login',
  standalone: false,
  templateUrl: './login.component.html'
})
export class LoginComponent implements OnInit {
  readonly loginForm: LoginForm = new FormGroup({
    email: new FormControl('', {
      nonNullable: true,
      validators: [Validators.required, Validators.email]
    }),
    password: new FormControl('', {
      nonNullable: true,
      validators: [Validators.required]
    })
  });

  isSubmitting = false;
  errorMessage = '';
  successMessage = '';

  constructor(
    private readonly authService: AuthService,
    private readonly router: Router,
    private readonly route: ActivatedRoute
  ) {}

  ngOnInit(): void {
    const email = this.route.snapshot.queryParamMap.get('email');
    const registered = this.route.snapshot.queryParamMap.get('registered') === 'true';

    if (email) {
      this.loginForm.controls.email.setValue(email);
    }

    if (registered) {
      this.successMessage = 'Conta criada com sucesso. Inicie sessão para continuar.';
    }
  }

  submit(): void {
    this.errorMessage = '';
    this.successMessage = '';

    if (this.loginForm.invalid) {
      this.loginForm.markAllAsTouched();
      return;
    }

    const credentials: LoginRequest = this.loginForm.getRawValue();
    this.isSubmitting = true;

    this.authService.login(credentials).pipe(
      finalize(() => {
        this.isSubmitting = false;
      })
    ).subscribe({
      next: (session) => {
        void this.router.navigateByUrl(this.getSafeReturnUrl(session.role));
      },
      error: (error: unknown) => {
        this.errorMessage = extractApiErrorMessage(
          error,
          'Não foi possível iniciar sessão. Verifique os dados e tente novamente.'
        );
      }
    });
  }

  hasError(controlName: keyof LoginForm['controls'], errorName: string): boolean {
    const control = this.loginForm.controls[controlName];
    return control.hasError(errorName) && (control.dirty || control.touched);
  }

  private getSafeReturnUrl(role: UserRole): string {
    const returnUrl = this.route.snapshot.queryParamMap.get('returnUrl');

    if (returnUrl && returnUrl.startsWith('/') && !returnUrl.startsWith('//')) {
      return returnUrl;
    }

    if (role === 'admin') {
      return '/admin-dashboard';
    }

    return '/dashboard';
  }
}
