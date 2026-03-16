import {AbstractControl, ValidationErrors} from '@angular/forms';

export interface PasswordRequirements {
    minLength: boolean;
    uppercase: boolean;
    lowercase: boolean;
    digit: boolean;
    specialChar: boolean;
}

export function getPasswordRequirements(value: string): PasswordRequirements {
    return {
        minLength: value.length >= 8,
        uppercase: /[A-Z]/.test(value),
        lowercase: /[a-z]/.test(value),
        digit: /[0-9]/.test(value),
        specialChar: /[^A-Za-z0-9]/.test(value),
    };
}

export function passwordValidator(control: AbstractControl): ValidationErrors | null {
    const value: string = control.value ?? '';
    if (value === '') {
        return null;
    }
    const req = getPasswordRequirements(value);
    const isValid = req.minLength && req.uppercase && req.lowercase && req.digit && req.specialChar;
    return isValid ? null : {passwordRequirements: true};
}
