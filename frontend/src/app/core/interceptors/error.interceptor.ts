import { HttpEvent, HttpHandler, HttpInterceptor,HttpRequest } from '@angular/common/http';
import { Injectable } from '@angular/core';
import {AuthenticationService} from '@app/services/authentication.service';
import { Observable, throwError } from 'rxjs';
import { catchError } from 'rxjs/operators';

@Injectable()
export class ErrorInterceptor implements HttpInterceptor {
    public constructor(private authorizationService: AuthenticationService) {}

    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    public intercept(request: HttpRequest<any>, next: HttpHandler): Observable<HttpEvent<any>> {
        return next.handle(request).pipe(catchError(err => {
            if ([401, 403].includes(err.status) && this.authorizationService.authenticationValue) {
                // auto logout if 401 or 403 response returned from api
                this.authorizationService.logout();
            }

            const error = err.error?.message || err.statusText;
            console.error(err);
            return throwError(error);
        }))
    }
}
