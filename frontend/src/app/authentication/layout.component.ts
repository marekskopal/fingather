import { Component } from '@angular/core';
import { Router } from '@angular/router';
import {AuthenticationService} from '@app/services/authentication.service';

@Component({ templateUrl: 'layout.component.html' })
export class LayoutComponent {
    public constructor(
        private router: Router,
        private authenticationService: AuthenticationService
    ) {
        // redirect to home if already logged in
        if (this.authenticationService.authenticationValue) {
            this.router.navigate(['/']);
        }
    }
}
