import {
    HttpErrorResponse, HttpEvent, HttpHandlerFn, HttpRequest,
} from '@angular/common/http';
import { inject } from '@angular/core';
import { Authentication } from '@app/models/authentication';
import { AuthenticationService } from '@app/services/authentication.service';
import { environment } from '@environments/environment';
import { catchError, from, Observable, shareReplay, switchMap, tap, throwError } from 'rxjs';

const refreshTokenUrl = `${environment.apiUrl}/authentication/refresh-token` as const;

// Shared in-flight refresh observable. All concurrent 401s subscribe to the same
// request so only one token refresh is made. Errors propagate to all callers and
// the reference is cleared on completion or error.
let refreshTokenObservable: Observable<Authentication> | null = null;

export function jwtInterceptor(req: HttpRequest<unknown>, next: HttpHandlerFn): Observable<HttpEvent<unknown>> {
    const authService = inject(AuthenticationService);

    req = addAuthHeader(req, authService);

    return next(req).pipe(
        catchError((err: HttpErrorResponse) => {
            if (
                [401, 403].includes(err.status)
                && authService.isLoggedIn()
                && req.url !== refreshTokenUrl
            ) {
                return handleTokenRefresh(req, next, authService);
            }

            return throwError(() => err);
        }),
    );
}

function addAuthHeader(req: HttpRequest<unknown>, authService: AuthenticationService): HttpRequest<unknown> {
    if (!authService.isLoggedIn()) {
        return req;
    }

    if (!req.url.startsWith(environment.apiUrl)) {
        return req;
    }

    return req.clone({
        setHeaders: {
            Authorization: `Bearer ${authService.authentication()?.accessToken}`,
        },
    });
}

function handleTokenRefresh(
    req: HttpRequest<unknown>,
    next: HttpHandlerFn,
    authService: AuthenticationService,
): Observable<HttpEvent<unknown>> {
    if (refreshTokenObservable === null) {
        refreshTokenObservable = from(authService.refreshToken()).pipe(
            tap({
                next: () => { refreshTokenObservable = null; },
                error: () => { refreshTokenObservable = null; },
            }),
            shareReplay(1),
        );
    }

    return refreshTokenObservable.pipe(
        switchMap(() => next(addAuthHeader(req, authService))),
        catchError((err) => {
            authService.logout();
            return throwError(() => err);
        }),
    );
}
