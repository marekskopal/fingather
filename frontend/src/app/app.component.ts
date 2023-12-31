import { Component } from '@angular/core';
import {AuthenticationService} from "@app/services/authentication.service";
import {Authentication} from "@app/models/authentication";


@Component({ selector: 'fingather-app', templateUrl: 'app.component.html' })
export class AppComponent {
    public authentication: Authentication|null;

    public constructor(private authenticationService: AuthenticationService) {
        this.authenticationService.authentication.subscribe(x => this.authentication = x);
    }

    public logout() {
        this.authenticationService.logout();
    }
}
