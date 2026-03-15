import {ChangeDetectionStrategy, Component, inject} from '@angular/core';
import {MatIconRegistry} from "@angular/material/icon";
import {RouterOutlet} from "@angular/router";
import {AuthenticationService} from "@app/services/authentication.service";
import {ContentLayoutService} from "@app/services/content-layout.service";
import {CurrentUserService} from "@app/services/current-user.service";
import {StorageService} from "@app/services/storage.service";
import {AlertComponent} from "@app/shared/components/alert/alert.component";
import {NavigationComponent} from "@app/shared/components/navigation/navigation.component";
import {NgbModule} from "@ng-bootstrap/ng-bootstrap";
import { TranslateService} from "@ngx-translate/core";

@Component({
    selector: 'fingather-app',
    templateUrl: 'app.component.html',
    imports: [
    NgbModule,
    NavigationComponent,
    AlertComponent,
    RouterOutlet,
],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class AppComponent {
    private readonly translateService = inject(TranslateService);
    private readonly matIconRegistry = inject(MatIconRegistry);
    private readonly contentLayoutService = inject(ContentLayoutService);
    private readonly storageService = inject(StorageService);
    private readonly authenticationService = inject(AuthenticationService);
    private readonly currentUserService = inject(CurrentUserService);

    protected readonly contentCenter = this.contentLayoutService.contentCenter;

    public constructor(
    ) {
        this.translateService.addLangs(['en', 'cs', 'de', 'es', 'fr']);
        this.translateService.setFallbackLang('en');

        const langParam = new URLSearchParams(window.location.search).get('lang');
        if (langParam && this.translateService.getLangs().includes(langParam)) {
            this.storageService.set('currentLanguage', langParam);
            this.translateService.use(langParam);
        } else {
            this.translateService.use(this.storageService.get<string>('currentLanguage') ?? 'en');
        }

        this.matIconRegistry.setDefaultFontSetClass('material-symbols-outlined');

        if (this.authenticationService.isLoggedIn()) {
            this.currentUserService.getCurrentUser().then((user) => {
                const supportedLangs = this.translateService.getLangs();
                if (supportedLangs.includes(user.locale) && user.locale !== this.translateService.currentLang) {
                    this.storageService.set('currentLanguage', user.locale);
                    this.translateService.use(user.locale);
                }
            }).catch(() => {
                // Ignore errors — locale sync is best-effort
            });
        }
    }
}
