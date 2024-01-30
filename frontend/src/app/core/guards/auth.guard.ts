import { Injectable } from '@angular/core';
import { ActivatedRouteSnapshot, Router, RouterStateSnapshot } from '@angular/router';
import {AuthenticationService} from '@app/services/authentication.service';

@Injectable({ providedIn: 'root' })
export class AuthGuard  {
    public constructor(
        private router: Router,
        private authenticationService: AuthenticationService
    ) {}

    public canActivate(route: ActivatedRouteSnapshot, state: RouterStateSnapshot): boolean {
        const authentication = this.authenticationService.authenticationValue;
        if (authentication) {
            // authorised so return true
            return true;
        }

        // not logged in so redirect to login page with the return url
        this.router.navigate(['/authentication/login'], { queryParams: { returnUrl: state.url }});
        return false;
    }
}
