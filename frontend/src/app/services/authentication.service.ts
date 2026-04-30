import { HttpClient } from '@angular/common/http';
import {computed, inject, Injectable, signal} from '@angular/core';
import { Router } from '@angular/router';
import { SignUp } from '@app/models';
import { Authentication } from '@app/models/authentication';
import { BoolResponse } from '@app/models/bool-response';
import {GoogleClientId} from "@app/models/google-client-id";
import { GoogleLoginResponse, isGoogleLoginRequiresCurrency } from '@app/models/google-login-response';
import { ImpersonationAuthentication } from '@app/models/impersonation-authentication';
import { ImpersonationState } from '@app/models/impersonation-state';
import { CurrentUserService } from '@app/services/current-user.service';
import { PortfolioService } from '@app/services/portfolio.service';
import { StorageService } from '@app/services/storage.service';
import { environment } from '@environments/environment';
import { TranslateService } from '@ngx-translate/core';
import { firstValueFrom } from 'rxjs';

const STORAGE_KEY_AUTH = 'authentication';
const STORAGE_KEY_ADMIN_AUTH = 'authentication:admin';
const STORAGE_KEY_IMPERSONATION = 'impersonation';

@Injectable({ providedIn: 'root' })
export class AuthenticationService {
    private readonly router = inject(Router);
    private readonly http = inject(HttpClient);
    private readonly portfolioService = inject(PortfolioService);
    private readonly currentUserService = inject(CurrentUserService);
    private readonly storageService = inject(StorageService);
    private readonly translateService = inject(TranslateService);

    public authentication = signal<Authentication | null>(
        this.storageService.get<Authentication>(STORAGE_KEY_AUTH),
    );
    public isLoggedIn = computed<boolean>(
        () => this.authentication() !== null,
    );

    public impersonation = signal<ImpersonationState | null>(
        this.storageService.get<ImpersonationState>(STORAGE_KEY_IMPERSONATION),
    );
    public isImpersonating = computed<boolean>(
        () => this.impersonation() !== null,
    );

    public async login(email: string, password: string): Promise<Authentication> {
        const authentication = await firstValueFrom<Authentication>(
            this.http.post<Authentication>(`${environment.apiUrl}/authentication/login`, {
                email,
                password,
            }),
        );

        return this.setAuthentication(authentication);
    }

    public async logout(): Promise<void> {
        if (this.isImpersonating()) {
            try {
                await this.stopImpersonation();
            } catch {
                this.clearImpersonationStorage();
            }
        }

        this.storageService.remove(STORAGE_KEY_AUTH);
        this.authentication.set(null);
        this.portfolioService.cleanCurrentPortfolio();
        this.currentUserService.cleanCurrentUser();
        this.router.navigate(['/authentication/login']);
    }

    public async signUp(signUp: SignUp): Promise<Authentication> {
        const authentication = await firstValueFrom<Authentication>(
            this.http.post<Authentication>(`${environment.apiUrl}/authentication/sign-up`, {
                ...signUp,
                locale: this.translateService.currentLang ?? 'en',
            }),
        );

        return this.setAuthentication(authentication);
    }

    public async isEmailExists(email: string): Promise<boolean> {
        const response = await firstValueFrom<BoolResponse>(
            this.http.post<BoolResponse>(`${environment.apiUrl}/authentication/email-exists`, {
                email,
            }),
        );

        return response.value;
    }

    public async googleClientId(): Promise<string> {
        const response = await firstValueFrom<GoogleClientId>(
            this.http.get<GoogleClientId>(`${environment.apiUrl}/authentication/google-client-id`),
        );

        return response.googleClientId;
    }

    public async googleLogin(idToken: string, defaultCurrencyId?: number): Promise<GoogleLoginResponse> {
        const response = await firstValueFrom<GoogleLoginResponse>(
            this.http.post<GoogleLoginResponse>(`${environment.apiUrl}/authentication/google-login`, {
                idToken,
                defaultCurrencyId,
                locale: this.translateService.currentLang ?? 'en',
            }),
        );

        if (!isGoogleLoginRequiresCurrency(response)) {
            this.setAuthentication(response);
        }

        return response;
    }

    public async requestPasswordReset(email: string): Promise<void> {
        await firstValueFrom(
            this.http.post(`${environment.apiUrl}/authentication/password-reset-request`, {email}),
        );
    }

    public async confirmPasswordReset(token: string, password: string): Promise<void> {
        await firstValueFrom(
            this.http.post(`${environment.apiUrl}/authentication/password-reset`, {token, password}),
        );
    }

    public async refreshToken(): Promise<Authentication> {
        const authentication = await firstValueFrom<Authentication>(
            this.http.post<Authentication>(
                `${environment.apiUrl}/authentication/refresh-token`,
                {
                    refreshToken: this.authentication()?.refreshToken,
                },
                { withCredentials: true },
            ),
        );

        this.storageService.set(STORAGE_KEY_AUTH, authentication);
        this.authentication.set(authentication);

        return authentication;
    }

    public async impersonate(userId: number): Promise<void> {
        const response = await firstValueFrom<ImpersonationAuthentication>(
            this.http.post<ImpersonationAuthentication>(
                `${environment.apiUrl}/admin/user/${userId}/impersonate`,
                {},
            ),
        );

        const adminAuth = this.authentication();
        if (adminAuth !== null) {
            this.storageService.set(STORAGE_KEY_ADMIN_AUTH, adminAuth);
        }

        const impersonationAuth: Authentication = {
            accessToken: response.accessToken,
            refreshToken: '',
            userId: response.targetUserId,
        };
        this.storageService.set(STORAGE_KEY_AUTH, impersonationAuth);
        this.authentication.set(impersonationAuth);

        const state: ImpersonationState = {
            sessionId: response.sessionId,
            targetUserId: response.targetUserId,
            targetUserEmail: response.targetUserEmail,
            targetUserName: response.targetUserName,
            expiresAt: response.expiresAt,
        };
        this.storageService.set(STORAGE_KEY_IMPERSONATION, state);
        this.impersonation.set(state);

        this.portfolioService.cleanCurrentPortfolio();
        this.currentUserService.cleanCurrentUser();

        this.router.navigate(['/']);
    }

    public async stopImpersonation(): Promise<void> {
        try {
            const adminAuth = await firstValueFrom<Authentication>(
                this.http.post<Authentication>(
                    `${environment.apiUrl}/authentication/stop-impersonation`,
                    {},
                ),
            );
            this.storageService.set(STORAGE_KEY_AUTH, adminAuth);
            this.authentication.set(adminAuth);
        } catch (error) {
            const stashed = this.storageService.get<Authentication>(STORAGE_KEY_ADMIN_AUTH);
            if (stashed === null) {
                this.clearImpersonationStorage();
                this.storageService.remove(STORAGE_KEY_AUTH);
                this.authentication.set(null);
                this.router.navigate(['/authentication/login']);
                throw error;
            }
            this.storageService.set(STORAGE_KEY_AUTH, stashed);
            this.authentication.set(stashed);
        }

        this.clearImpersonationStorage();
        this.portfolioService.cleanCurrentPortfolio();
        this.currentUserService.cleanCurrentUser();
    }

    private clearImpersonationStorage(): void {
        this.storageService.remove(STORAGE_KEY_ADMIN_AUTH);
        this.storageService.remove(STORAGE_KEY_IMPERSONATION);
        this.impersonation.set(null);
    }

    private setAuthentication(authentication: Authentication): Authentication {
        this.storageService.set(STORAGE_KEY_AUTH, authentication);
        this.authentication.set(authentication);
        this.portfolioService.cleanCurrentPortfolio();
        this.currentUserService.cleanCurrentUser();

        return authentication;
    }
}
