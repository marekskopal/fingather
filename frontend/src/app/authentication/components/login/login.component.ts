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
    styleUrl: 'login.component.scss',
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
            const container = document.getElementById('googleButtonContainer');
            if (window.google?.accounts?.id && container) {
                window.google.accounts.id.initialize({
                    client_id: googleClientId,
                    callback: (response) => this.handleGoogleCallback(response),
                });

                window.google.accounts.id.renderButton(container, {
                    type: 'standard',
                    theme: 'filled_black',
                    size: 'large',
                    text: 'signin_with',
                    shape: 'rectangular',
                    width: container.offsetWidth,
                });
            } else {
                setTimeout(checkGoogle, 100);
            }
        };

        checkGoogle();
    }

    private handleGoogleCallback(response: google.accounts.id.CredentialResponse): void {
        this.ngZone.run(async () => {
            this.googleLoading.set(true);
            this.alertService.clear();

            try {
                const result = await this.authorizationService.googleLogin(response.credential);

                if (isGoogleLoginRequiresCurrency(result)) {
                    this.router.navigate(['/authentication/google-sign-up'], {
                        queryParams: {
                            idToken: response.credential,
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
            } else if (error !== null && typeof error === 'object' && 'error' in error && typeof (error as {error: unknown}).error === 'string') {
                this.alertService.error((error as {error: string}).error);
            } else {
                this.alertService.error('Login failed. Please check your credentials.');
            }
        } finally {
            this.saving.set(false);
        }
    }
}
