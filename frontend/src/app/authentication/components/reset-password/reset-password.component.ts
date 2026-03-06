import {ChangeDetectionStrategy, Component, inject, OnInit, signal} from '@angular/core';
import {AbstractControl, ReactiveFormsModule, ValidationErrors, Validators} from '@angular/forms';
import {ActivatedRoute, RouterLink} from '@angular/router';
import { AuthenticationService } from '@app/services/authentication.service';
import { BaseForm } from '@app/shared/components/form/base-form';
import {InputValidatorComponent} from '@app/shared/components/input-validator/input-validator.component';
import {SaveButtonComponent} from '@app/shared/components/save-button/save-button.component';
import {TranslatePipe} from '@ngx-translate/core';

@Component({
    templateUrl: 'reset-password.component.html',
    imports: [
        ReactiveFormsModule,
        TranslatePipe,
        InputValidatorComponent,
        SaveButtonComponent,
        RouterLink,
    ],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ResetPasswordComponent extends BaseForm implements OnInit {
    private readonly authenticationService = inject(AuthenticationService);
    private readonly route = inject(ActivatedRoute);

    protected readonly success = signal<boolean>(false);
    protected readonly tokenInvalid = signal<boolean>(false);

    private token: string = '';

    public ngOnInit(): void {
        this.token = this.route.snapshot.params['token'] ?? '';

        this.form = this.formBuilder.group(
            {
                password: ['', [Validators.required, Validators.minLength(6)]],
                confirmPassword: ['', Validators.required],
            },
            {validators: this.passwordsMatchValidator},
        );
    }

    private passwordsMatchValidator(control: AbstractControl): ValidationErrors | null {
        const password = control.get('password')?.value;
        const confirmPassword = control.get('confirmPassword')?.value;
        return password === confirmPassword ? null : {passwordsMismatch: true};
    }

    public async onSubmit(): Promise<void> {
        this.submitted.set(true);
        this.alertService.clear();

        if (this.form.invalid) {
            return;
        }

        this.saving.set(true);
        try {
            await this.authenticationService.confirmPasswordReset(this.token, this.f['password'].value);
            this.success.set(true);
        } catch {
            this.tokenInvalid.set(true);
        } finally {
            this.saving.set(false);
        }
    }
}
