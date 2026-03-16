import {ChangeDetectionStrategy, Component, computed, input} from '@angular/core';
import {toObservable, toSignal} from '@angular/core/rxjs-interop';
import {AbstractControl} from '@angular/forms';
import {MatIcon} from '@angular/material/icon';
import {getPasswordRequirements, PasswordRequirements} from '@app/authentication/validator/password.validator';
import {TranslatePipe} from '@ngx-translate/core';
import {startWith, switchMap} from 'rxjs';

@Component({
    selector: 'fingather-password-requirements',
    templateUrl: 'password-requirements.component.html',
    styleUrl: 'password-requirements.component.scss',
    imports: [MatIcon, TranslatePipe],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class PasswordRequirementsComponent {
    public readonly control = input.required<AbstractControl>();

    protected readonly value = toSignal(
        toObservable(this.control).pipe(
            switchMap((control) => control.valueChanges.pipe(startWith(control.value))),
        ),
        {initialValue: ''},
    );

    protected readonly requirements = computed<PasswordRequirements>(() => getPasswordRequirements(this.value() ?? ''));
}
