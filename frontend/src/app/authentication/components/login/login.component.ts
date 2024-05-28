import { ChangeDetectionStrategy, Component, OnInit } from '@angular/core';
import { UntypedFormBuilder, Validators } from '@angular/forms';
import { Router } from '@angular/router';
import { AlertService, CurrentUserService } from '@app/services';
import { AuthenticationService } from '@app/services/authentication.service';
import { BaseForm } from '@app/shared/components/form/base-form';

@Component({
    templateUrl: 'login.component.html',
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class LoginComponent extends BaseForm implements OnInit {
    public constructor(
        private readonly router: Router,
        private readonly authorizationService: AuthenticationService,
        private readonly currentUserService: CurrentUserService,
        formBuilder: UntypedFormBuilder,
        alertService: AlertService,
    ) {
        super(formBuilder, alertService);
    }

    public ngOnInit(): void {
        this.form = this.formBuilder.group({
            email: ['', [Validators.required, Validators.email]],
            password: ['', Validators.required]
        });
    }

    public async onSubmit(): Promise<void> {
        this.submitted = true;

        // reset alerts on submit
        this.alertService.clear();

        // stop here if form is invalid
        if (this.form.invalid) {
            return;
        }

        this.loading = true;
        try {
            await this.authorizationService.login(this.f['email'].value, this.f['password'].value);

            const currentUser = await this.currentUserService.getCurrentUser();
            const returnUrl = currentUser.isOnboardingCompleted ? '/' : '/onboarding';

            this.router.navigateByUrl(returnUrl);
        } catch (error) {
            if (error instanceof Error) {
                this.alertService.error(error.message);
            }

            this.loading = false;
        }
    }
}
