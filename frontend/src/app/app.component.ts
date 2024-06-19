import { ChangeDetectionStrategy, Component, computed } from '@angular/core';
import { AuthenticationService } from '@app/services/authentication.service';
import { TranslateService } from '@ngx-translate/core';

@Component({
    selector: 'fingather-app',
    templateUrl: 'app.component.html',
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class AppComponent {
    public isNavigationCollapsed: boolean = true;

    public languages: string[];
    public currentLanguage: string;

    protected $isLoggedIn = computed<boolean>(() => this.authenticationService.$isLoggedIn());

    public constructor(
        private readonly authenticationService: AuthenticationService,
        private readonly translateService: TranslateService,
    ) {
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
