import { Injectable } from '@angular/core';
import { Router } from '@angular/router';
import { HttpClient } from '@angular/common/http';
import { BehaviorSubject, Observable } from 'rxjs';
import {map} from 'rxjs/operators';

import { environment } from '@environments/environment';
import {Authentication} from "@app/models/authentication";
import {OkResponse} from "@app/models/ok-response";
import {BoolResponse} from "@app/models/bool-response";
import {SignUp} from "@app/models";

@Injectable({ providedIn: 'root' })
export class AuthenticationService {
    private authenticationSubject: BehaviorSubject<Authentication|null>;
    public authentication: Observable<Authentication|null>;
    private refreshTokenTimeout: ReturnType<typeof setTimeout>;

    public constructor(
        private router: Router,
        private http: HttpClient
    ) {
        const localStorageAuthentication = localStorage.getItem('authentication');
        const authentication = localStorageAuthentication !== null ? JSON.parse(localStorageAuthentication) : null;

        this.authenticationSubject = new BehaviorSubject<Authentication | null>(authentication);
        this.authentication = this.authenticationSubject.asObservable();
    }

    public get authenticationValue(): Authentication|null {
        return this.authenticationSubject.value;
    }

    public login(email: string, password: string): Observable<Authentication> {
        return this.http.post<Authentication>(`${environment.apiUrl}/authentication/login`, {
          email: email,
          password,
        })
            .pipe(map((authentication: Authentication) => {
                // store user details and jwt token in local storage to keep user logged in between page refreshes
                localStorage.setItem('authentication', JSON.stringify(authentication));
                this.authenticationSubject.next(authentication);
                this.startRefreshTokenTimer();
                return authentication;
            }));
    }

    public logout(): void {
        // remove authentication from local storage and set current authentication to null
        localStorage.removeItem('authentication');
        this.authenticationSubject.next(null);
        this.stopRefreshTokenTimer();
        this.router.navigate(['/authentication/login']);
    }

    public signUp(signUp: SignUp): Observable<OkResponse> {
        return this.http.post<OkResponse>(`${environment.apiUrl}/authentication/sign-up`, signUp)
    }

    public isEmailExists(email: string): Observable<boolean>
    {
        return this.http.post<BoolResponse>(`${environment.apiUrl}/authentication/email-exists`, {
            email: email,
        }).pipe(
            map(response => response.value)
        );
    }

    public refreshToken(): Observable<Authentication> {
        return this.http.post<Authentication>(`${environment.apiUrl}/authentication/refresh-token`, {}, { withCredentials: true })
            .pipe(map((authentication: Authentication) => {
                localStorage.setItem('authentication', JSON.stringify(authentication));
                this.authenticationSubject.next(authentication);
                this.startRefreshTokenTimer();
                return authentication;
            }));
    }

    private startRefreshTokenTimer(): void {
        // parse json object from base64 encoded jwt token
        const jwtBase64 = this.authenticationValue!.token!.split('.')[1];
        const jwtToken = JSON.parse(atob(jwtBase64));

        // set a timeout to refresh the token a minute before it expires
        const expires = new Date(jwtToken.exp * 1000);
        const timeout = expires.getTime() - Date.now() - (60 * 1000);
        this.refreshTokenTimeout = setTimeout(() => this.refreshToken().subscribe(), timeout);
    }

    private stopRefreshTokenTimer(): void {
        clearTimeout(this.refreshTokenTimeout);
    }
}
