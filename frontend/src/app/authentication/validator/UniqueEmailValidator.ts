import { Injectable } from '@angular/core';
import { AbstractControl, AsyncValidator, ValidationErrors } from '@angular/forms';
import { AuthenticationService } from '@app/services/authentication.service';
import { Observable, of } from 'rxjs';
import { catchError, map } from 'rxjs/operators';

@Injectable({ providedIn: 'root' })
export class UniqueEmailValidator implements AsyncValidator {
    public constructor(
        private readonly authenticationService: AuthenticationService
    ) {}

    public validate(control: AbstractControl): Observable<ValidationErrors | null> {
        return this.authenticationService.isEmailExists(control.value).pipe(
            map((isEmailExists: boolean) => (isEmailExists ? { uniqueEmail: true } : null)),
            catchError(() => of(null)),
        );
    }
}
