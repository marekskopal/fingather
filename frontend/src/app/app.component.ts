import { Component } from '@angular/core';
import { Authentication } from '@app/models/authentication';
import { AuthenticationService } from '@app/services/authentication.service';

@Component({ selector: 'fingather-app', templateUrl: 'app.component.html' })
export class AppComponent {
    public authentication: Authentication | null;

    public isNavigationCollapsed: boolean = true;

    public constructor(private readonly authenticationService: AuthenticationService) {
        this.authenticationService.authentication.subscribe((x) => this.authentication = x);
    }

    public logout(): void {
        this.authenticationService.logout();
    }

    public toggleNavigation(): void {
        this.isNavigationCollapsed = !this.isNavigationCollapsed;
    }

    public collapseNavigation(): void {
        this.isNavigationCollapsed = true;
    }
}
