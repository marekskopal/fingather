import {
    AfterViewInit,
    ChangeDetectionStrategy,
    Component,
    inject,
    NgZone,
    OnInit,
    signal,
} from '@angular/core';
import {ReactiveFormsModule, Validators} from '@angular/forms';
import {Router, RouterLink} from '@angular/router';
import { isGoogleLoginRequiresCurrency } from '@app/models/google-login-response';
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
export class LoginComponent extends BaseForm implements OnInit, AfterViewInit {
    private readonly router = inject(Router);
    private readonly ngZone = inject(NgZone);
    private readonly authorizationService = inject(AuthenticationService);
    private readonly currentUserService = inject( CurrentUserService);

    protected readonly googleLoading = signal<boolean>(false);
    protected readonly googleReady = signal<boolean>(false);
    protected readonly googleEnabled = signal<boolean>(false);

    public ngOnInit(): void {
        this.form = this.formBuilder.group({
            email: ['', [Validators.required, Validators.email]],
            password: ['', Validators.required],
        });
    }

    public async ngAfterViewInit(): Promise<void> {
        const googleClientId = await this.authorizationService.googleClientId();
        if (googleClientId === '') {
            return;
        }

        this.googleEnabled.set(true);

        this.initializeGoogleSignIn(googleClientId);
    }

    private initializeGoogleSignIn(googleClientId: string): void {
        const checkGoogle = (): void => {
            if (window.google?.accounts?.id) {
                window.google.accounts.id.initialize({
                    client_id: googleClientId,
                    callback: (response) => this.handleGoogleCallback(response),
                    prompt_parent_id: 'googleButtonContainer',
                    use_fedcm_for_prompt: false,
                });

                this.googleReady.set(true);
            } else {
                setTimeout(checkGoogle, 100);
            }
        };

        checkGoogle();
    }

    protected onGoogleSignIn(): void {
        if (!this.googleReady() || this.googleLoading()) {
            return;
        }

        window.google?.accounts.id.prompt();
    }

    private handleGoogleCallback(response: google.accounts.id.CredentialResponse): void {
        this.ngZone.run(async () => {
            this.googleLoading.set(true);
            this.alertService.clear();

            try {
                const result = await this.authorizationService.googleLogin(response.credential);

                if (isGoogleLoginRequiresCurrency(result)) {
                    this.router.navigate(['/authentication/google-sign-up'], {
                        state: {
                            idToken: response.credential,
                            email: result.email,
                            name: result.name,
                        },
                    });
                    return;
                }

                const currentUser = await this.currentUserService.getCurrentUser();
                const returnUrl = currentUser.isOnboardingCompleted ? '/' : '/onboarding/step-one';
                this.router.navigateByUrl(returnUrl);
            } catch (error) {
                if (error instanceof Error) {
                    this.alertService.error(error.message);
                }
            } finally {
                this.googleLoading.set(false);
            }
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
