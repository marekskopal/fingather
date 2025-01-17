import {ChangeDetectionStrategy, Component, inject, OnInit} from '@angular/core';
import {ReactiveFormsModule, Validators} from '@angular/forms';
import {Router, RouterLink} from '@angular/router';
import { CurrentUserService } from '@app/services';
import { AuthenticationService } from '@app/services/authentication.service';
import { BaseForm } from '@app/shared/components/form/base-form';
import {InputValidatorComponent} from "@app/shared/components/input-validator/input-validator.component";
import {SaveButtonComponent} from "@app/shared/components/save-button/save-button.component";
import {TranslatePipe} from "@ngx-translate/core";

@Component({
    templateUrl: 'login.component.html',
    imports: [
        ReactiveFormsModule,
        TranslatePipe,
        InputValidatorComponent,
        SaveButtonComponent,
        RouterLink,
    ],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class LoginComponent extends BaseForm implements OnInit {
    private readonly router = inject(Router);
    private readonly authorizationService = inject(AuthenticationService);
    private readonly currentUserService = inject( CurrentUserService);

    public ngOnInit(): void {
        this.form = this.formBuilder.group({
            email: ['', [Validators.required, Validators.email]],
            password: ['', Validators.required],
        });
    }

    public async onSubmit(): Promise<void> {
        this.submitted.set(true);

        // reset alerts on submit
        this.alertService.clear();

        // stop here if form is invalid
        if (this.form.invalid) {
            return;
        }

        this.saving.set(true);
        try {
            await this.authorizationService.login(this.f['email'].value, this.f['password'].value);

            const currentUser = await this.currentUserService.getCurrentUser();
            const returnUrl = currentUser.isOnboardingCompleted ? '/' : '/onboarding/step-one';

            this.router.navigateByUrl(returnUrl);
        } catch (error) {
            if (error instanceof Error) {
                this.alertService.error(error.message);
            }
        } finally {
            this.saving.set(false);
        }
    }
}
