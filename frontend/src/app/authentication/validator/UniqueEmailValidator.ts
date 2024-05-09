import { Injectable } from '@angular/core';
import { AbstractControl, AsyncValidator, ValidationErrors } from '@angular/forms';
import { AuthenticationService } from '@app/services/authentication.service';

@Injectable({ providedIn: 'root' })
export class UniqueEmailValidator implements AsyncValidator {
    public constructor(
        private readonly authenticationService: AuthenticationService
    ) {}

    public async validate(control: AbstractControl): Promise<ValidationErrors | null> {
        const isEmailExists = await this.authenticationService.isEmailExists(control.value);

        return isEmailExists ? { uniqueEmail: true } : null;
    }
}
