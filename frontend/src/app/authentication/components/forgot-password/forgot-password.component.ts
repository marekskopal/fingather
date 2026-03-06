import {ChangeDetectionStrategy, Component, inject, OnInit, signal} from '@angular/core';
import {ReactiveFormsModule, Validators} from '@angular/forms';
import {RouterLink} from '@angular/router';
import { AuthenticationService } from '@app/services/authentication.service';
import { BaseForm } from '@app/shared/components/form/base-form';
import {InputValidatorComponent} from '@app/shared/components/input-validator/input-validator.component';
import {SaveButtonComponent} from '@app/shared/components/save-button/save-button.component';
import {TranslatePipe} from '@ngx-translate/core';

@Component({
    templateUrl: 'forgot-password.component.html',
    imports: [
        ReactiveFormsModule,
        TranslatePipe,
        InputValidatorComponent,
        SaveButtonComponent,
        RouterLink,
    ],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ForgotPasswordComponent extends BaseForm implements OnInit {
    private readonly authenticationService = inject(AuthenticationService);

    protected readonly submitted$ = signal<boolean>(false);

    public ngOnInit(): void {
        this.form = this.formBuilder.group({
            email: ['', [Validators.required, Validators.email]],
        });
    }

    public async onSubmit(): Promise<void> {
        this.submitted.set(true);
        this.alertService.clear();

        if (this.form.invalid) {
            return;
        }

        this.saving.set(true);
        try {
            await this.authenticationService.requestPasswordReset(this.f['email'].value);
            this.submitted$.set(true);
        } catch {
            // Silently ignore errors to not leak whether email exists
            this.submitted$.set(true);
        } finally {
            this.saving.set(false);
        }
    }
}
