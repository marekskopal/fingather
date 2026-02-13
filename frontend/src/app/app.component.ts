import {ChangeDetectionStrategy, Component, inject} from '@angular/core';
import {MatIconRegistry} from "@angular/material/icon";
import {RouterOutlet} from "@angular/router";
import {ContentLayoutService} from "@app/services/content-layout.service";
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

    protected readonly contentCenter = this.contentLayoutService.contentCenter;

    public constructor(
    ) {
        this.translateService.addLangs(['en', 'cs', 'de', 'es', 'fr']);
        this.translateService.setFallbackLang('en');

        const langParam = new URLSearchParams(window.location.search).get('lang');
        if (langParam && this.translateService.getLangs().includes(langParam)) {
            localStorage.setItem('currentLanguage', langParam);
            this.translateService.use(langParam);
        } else {
            this.translateService.use(localStorage.getItem('currentLanguage') ?? 'en');
        }

        this.matIconRegistry.setDefaultFontSetClass('material-symbols-outlined');
    }
}
