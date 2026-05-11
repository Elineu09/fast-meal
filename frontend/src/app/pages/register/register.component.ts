import { Component } from '@angular/core';
import { FormControl, FormGroup, Validators } from '@angular/forms';
import { Router } from '@angular/router';
import { finalize } from 'rxjs';

import { RegisterRequest } from '../../models/auth.model';
import { AuthService } from '../../services/auth.service';
import { extractApiErrorMessage } from '../../shared/helpers/http-error.helper';
import {
  fieldsMatchValidator,
  strongPasswordValidator
} from '../../shared/validators/password.validator';

type RegisterForm = FormGroup<{
  nome: FormControl<string>;
  email: FormControl<string>;
  password: FormControl<string>;
  confirmPassword: FormControl<string>;
}>;

@Component({
  selector: 'app-register',
  standalone: false,
  templateUrl: './register.component.html'
})
export class RegisterComponent {
  readonly registerForm: RegisterForm = new FormGroup({
    nome: new FormControl('', {
      nonNullable: true,
      validators: [Validators.required, Validators.minLength(2)]
    }),
    email: new FormControl('', {
      nonNullable: true,
      validators: [Validators.required, Validators.email]
    }),
    password: new FormControl('', {
      nonNullable: true,
      validators: [Validators.required, strongPasswordValidator]
    }),
    confirmPassword: new FormControl('', {
      nonNullable: true,
      validators: [Validators.required]
    })
  }, {
    validators: [fieldsMatchValidator('password', 'confirmPassword')]
  });

  isSubmitting = false;
  errorMessage = '';

  constructor(
    private readonly authService: AuthService,
    private readonly router: Router
  ) {}

  submit(): void {
    this.errorMessage = '';

    if (this.registerForm.invalid) {
      this.registerForm.markAllAsTouched();
      return;
    }

    const formValue = this.registerForm.getRawValue();
    const payload: RegisterRequest = {
      nome: formValue.nome.trim(),
      email: formValue.email.trim().toLowerCase(),
      password: formValue.password
    };

    this.isSubmitting = true;

    this.authService.register(payload).pipe(
      finalize(() => {
        this.isSubmitting = false;
      })
    ).subscribe({
      next: () => {
        void this.router.navigate(['/login'], {
          queryParams: {
            registered: true,
            email: payload.email
          }
        });
      },
      error: (error: unknown) => {
        this.errorMessage = extractApiErrorMessage(
          error,
          'Não foi possível criar a conta. Verifique os dados e tente novamente.'
        );
      }
    });
  }

  hasError(controlName: keyof RegisterForm['controls'], errorName: string): boolean {
    const control = this.registerForm.controls[controlName];
    return control.hasError(errorName) && (control.dirty || control.touched);
  }

  passwordsDoNotMatch(): boolean {
    const confirmPassword = this.registerForm.controls.confirmPassword;
    return (
      this.registerForm.hasError('fieldsMismatch') &&
      (confirmPassword.dirty || confirmPassword.touched)
    );
  }
}
