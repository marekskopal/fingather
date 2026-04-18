import {NgOptimizedImage} from '@angular/common';
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
import {ActivatedRoute} from '@angular/router';
import {isGoogleLoginRequiresCurrency} from '@app/models/google-login-response';
import {CurrentUserService} from '@app/services';
import {AuthenticationService} from '@app/services/authentication.service';
import {OAuthService} from '@app/services/oauth.service';
import {BaseForm} from '@app/shared/components/form/base-form';
import {InputValidatorComponent} from '@app/shared/components/input-validator/input-validator.component';
import {SaveButtonComponent} from '@app/shared/components/save-button/save-button.component';
import {TranslatePipe} from '@ngx-translate/core';

@Component({
    templateUrl: 'oauth-authorize.component.html',
    styleUrl: 'oauth-authorize.component.scss',
    imports: [
        NgOptimizedImage,
        ReactiveFormsModule,
        TranslatePipe,
        InputValidatorComponent,
        SaveButtonComponent,
    ],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class OAuthAuthorizeComponent extends BaseForm implements OnInit, AfterViewInit {
    private readonly route = inject(ActivatedRoute);
    private readonly ngZone = inject(NgZone);
    private readonly authenticationService = inject(AuthenticationService);
    private readonly currentUserService = inject(CurrentUserService);
    private readonly oauthService = inject(OAuthService);

    protected readonly clientName = signal<string>('');
    protected readonly isLoggedIn = this.authenticationService.isLoggedIn;
    protected readonly authorizing = signal<boolean>(false);
    protected readonly googleLoading = signal<boolean>(false);
    protected readonly googleEnabled = signal<boolean>(false);
    protected readonly error = signal<string>('');

    private clientId = '';
    private redirectUri = '';
    private codeChallenge = '';
    private codeChallengeMethod = '';
    private state = '';

    public ngOnInit(): void {
        this.form = this.formBuilder.group({
            email: ['', [Validators.required, Validators.email]],
            password: ['', Validators.required],
        });

        const params = this.route.snapshot.queryParams;
        this.clientId = params['client_id'] ?? '';
        this.redirectUri = params['redirect_uri'] ?? '';
        this.codeChallenge = params['code_challenge'] ?? '';
        this.codeChallengeMethod = params['code_challenge_method'] ?? '';
        this.state = params['state'] ?? '';

        if (params['response_type'] !== 'code' || this.clientId === '') {
            this.error.set('Invalid OAuth request parameters.');
            return;
        }

        this.loadClientInfo();
    }

    public async ngAfterViewInit(): Promise<void> {
        if (this.isLoggedIn()) {
            return;
        }

        const googleClientId = await this.authenticationService.googleClientId();
        if (googleClientId === '') {
            return;
        }

        this.googleEnabled.set(true);
        this.initializeGoogleSignIn(googleClientId);
    }

    private async loadClientInfo(): Promise<void> {
        try {
            const info = await this.oauthService.getClientInfo(this.clientId);
            this.clientName.set(info.clientName);
        } catch {
            this.error.set('Unknown application.');
        }
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
                const result = await this.authenticationService.googleLogin(response.credential);

                if (isGoogleLoginRequiresCurrency(result)) {
                    this.error.set('Please complete your account setup first.');
                    return;
                }

                await this.performAuthorize();
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
        this.alertService.clear();

        if (this.form.invalid) {
            return;
        }

        this.saving.set(true);
        try {
            await this.authenticationService.login(this.f['email'].value, this.f['password'].value);
            await this.performAuthorize();
        } catch (error) {
            if (error instanceof Error) {
                this.alertService.error(error.message);
            } else if (
                error !== null
                && typeof error === 'object'
                && 'error' in error
                && typeof (error as {error: unknown}).error === 'string'
            ) {
                this.alertService.error((error as {error: string}).error);
            } else {
                this.alertService.error('Login failed. Please check your credentials.');
            }
        } finally {
            this.saving.set(false);
        }
    }

    protected async onAuthorize(): Promise<void> {
        this.authorizing.set(true);
        this.alertService.clear();

        try {
            await this.performAuthorize();
        } catch (error) {
            if (error instanceof Error) {
                this.alertService.error(error.message);
            }
        } finally {
            this.authorizing.set(false);
        }
    }

    private async performAuthorize(): Promise<void> {
        const response = await this.oauthService.authorize({
            clientId: this.clientId,
            redirectUri: this.redirectUri,
            codeChallenge: this.codeChallenge,
            codeChallengeMethod: this.codeChallengeMethod,
            state: this.state,
        });

        const params = new URLSearchParams({code: response.code});
        if (response.state) {
            params.set('state', response.state);
        }

        window.location.href = `${response.redirectUri}?${params.toString()}`;
    }
}
