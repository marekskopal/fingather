import { Component } from '@angular/core';
import { Authentication } from '@app/models/authentication';
import { AuthenticationService } from '@app/services/authentication.service';
import { TranslateService } from '@ngx-translate/core';

@Component({ selector: 'fingather-app', templateUrl: 'app.component.html' })
export class AppComponent {
    public authentication: Authentication | null;

    public isNavigationCollapsed: boolean = true;

    public languages: string[];
    public currentLanguage: string;

    public constructor(
        private readonly authenticationService: AuthenticationService,
        private readonly translateService: TranslateService,
    ) {
        this.authenticationService.authentication.subscribe((x) => this.authentication = x);
        this.languages = translateService.getLangs();
        this.currentLanguage = translateService.currentLang;
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

    public changeLanguage(lang: string): void {
        localStorage.setItem('currentLanguage', lang);
        this.translateService.use(lang);
        this.currentLanguage = this.translateService.currentLang;
    }
}
