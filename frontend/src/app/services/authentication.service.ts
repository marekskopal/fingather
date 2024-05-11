import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Router } from '@angular/router';
import { SignUp } from '@app/models';
import { Authentication } from '@app/models/authentication';
import { BoolResponse } from '@app/models/bool-response';
import { OkResponse } from '@app/models/ok-response';
import { CurrentUserService } from '@app/services/current-user.service';
import { PortfolioService } from '@app/services/portfolio.service';
import { environment } from '@environments/environment';
import { BehaviorSubject, firstValueFrom, Observable } from 'rxjs';

@Injectable({ providedIn: 'root' })
export class AuthenticationService {
    private authenticationSubject: BehaviorSubject<Authentication | null>;
    public authentication: Observable<Authentication | null>;
    private refreshTokenTimeout: ReturnType<typeof setTimeout>;

    public constructor(
        private readonly router: Router,
        private readonly http: HttpClient,
        private readonly portfolioService: PortfolioService,
        private readonly currentUserService: CurrentUserService,
    ) {
        const localStorageAuthentication = localStorage.getItem('authentication');
        const authentication = localStorageAuthentication !== null ? JSON.parse(localStorageAuthentication) : null;

        this.authenticationSubject = new BehaviorSubject<Authentication | null>(authentication);
        this.authentication = this.authenticationSubject.asObservable();
    }

    public get authenticationValue(): Authentication | null {
        return this.authenticationSubject.value;
    }

    public async login(email: string, password: string): Promise<Authentication> {
        const authentication = await firstValueFrom<Authentication>(
            this.http.post<Authentication>(`${environment.apiUrl}/authentication/login`, {
                email,
                password,
            })
        );

        // store user details and jwt token in local storage to keep user logged in between page refreshes
        localStorage.setItem('authentication', JSON.stringify(authentication));
        this.authenticationSubject.next(authentication);
        this.startRefreshTokenTimer();
        this.portfolioService.cleanCurrentPortfolio();
        this.currentUserService.cleanCurrentUser();

        return authentication;
    }

    public logout(): void {
        // remove authentication from local storage and set current authentication to null
        localStorage.removeItem('authentication');
        this.authenticationSubject.next(null);
        this.stopRefreshTokenTimer();
        this.portfolioService.cleanCurrentPortfolio();
        this.currentUserService.cleanCurrentUser();
        this.router.navigate(['/authentication/login']);
    }

    public signUp(signUp: SignUp): Promise<OkResponse> {
        return firstValueFrom<OkResponse>(
            this.http.post<OkResponse>(`${environment.apiUrl}/authentication/sign-up`, signUp)
        );
    }

    public async isEmailExists(email: string): Promise<boolean> {
        const response = await firstValueFrom<BoolResponse>(
            this.http.post<BoolResponse>(`${environment.apiUrl}/authentication/email-exists`, {
                email,
            })
        );

        return response.value;
    }

    public async refreshToken(): Promise<Authentication> {
        const authentication = await firstValueFrom<Authentication>(
            this.http.post<Authentication>(
                `${environment.apiUrl}/authentication/refresh-token`,
                {},
                { withCredentials: true }
            )
        );

        localStorage.setItem('authentication', JSON.stringify(authentication));
        this.authenticationSubject.next(authentication);
        this.startRefreshTokenTimer();

        return authentication;
    }

    private startRefreshTokenTimer(): void {
        // parse json object from base64 encoded jwt token
        const jwtBase64 = this.authenticationValue!.token!.split('.')[1];
        const jwtToken = JSON.parse(atob(jwtBase64));

        // set a timeout to refresh the token a minute before it expires
        const expires = new Date(jwtToken.exp * 1000);
        const timeout = expires.getTime() - Date.now() - (60 * 1000);
        this.refreshTokenTimeout = setTimeout(() => this.refreshToken(), timeout);
    }

    private stopRefreshTokenTimer(): void {
        clearTimeout(this.refreshTokenTimeout);
    }
}
