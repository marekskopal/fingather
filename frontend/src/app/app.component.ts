import { Component } from '@angular/core';
import {AuthenticationService} from "@app/services/authentication.service";
import {Authentication} from "@app/models/authentication";


@Component({ templateUrl: 'app.component.html' })
export class AppComponent {
    authentication: Authentication|null;

    constructor(private authenticationService: AuthenticationService) {
        this.authenticationService.authentication.subscribe(x => this.authentication = x);
    }

    logout() {
        this.authenticationService.logout();
    }
}
