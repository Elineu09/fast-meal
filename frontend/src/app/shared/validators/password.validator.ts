import { AbstractControl, ValidationErrors, ValidatorFn } from '@angular/forms';

const strongPasswordPattern = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^\w\s]).{8,}$/;

export const strongPasswordValidator: ValidatorFn = (control: AbstractControl): ValidationErrors | null => {
  const value = String(control.value ?? '');

  if (!value || strongPasswordPattern.test(value)) {
    return null;
  }

  return {
    weakPassword: true
  };
};

export function fieldsMatchValidator(
  firstControlName: string,
  secondControlName: string
): ValidatorFn {
  return (group: AbstractControl): ValidationErrors | null => {
    const firstValue = group.get(firstControlName)?.value;
    const secondValue = group.get(secondControlName)?.value;

    if (!firstValue || !secondValue || firstValue === secondValue) {
      return null;
    }

    return {
      fieldsMismatch: true
    };
  };
}
