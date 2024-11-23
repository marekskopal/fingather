import {CommonModule} from "@angular/common";
import { HttpClient} from "@angular/common/http";
import {ChangeDetectionStrategy, Component, inject} from '@angular/core';
import {MatIconRegistry} from "@angular/material/icon";
import { RouterOutlet} from "@angular/router";
import {ContentLayoutService} from "@app/services/content-layout.service";
import {AlertComponent} from "@app/shared/components/alert/alert.component";
import {NavigationComponent} from "@app/shared/components/navigation/navigation.component";
import {NgbModule} from "@ng-bootstrap/ng-bootstrap";
import { TranslateService} from "@ngx-translate/core";
import {TranslateHttpLoader} from "@ngx-translate/http-loader";

export function HttpLoaderFactory(http: HttpClient): TranslateHttpLoader {
    return new TranslateHttpLoader(http, '/i18n/');
}

@Component({
    selector: 'fingather-app',
    templateUrl: 'app.component.html',
    imports: [
        CommonModule,
        NgbModule,
        NavigationComponent,
        AlertComponent,
        RouterOutlet,
    ],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class AppComponent {
    private readonly contentLayoutService = inject(ContentLayoutService);

    protected readonly contentCenter = this.contentLayoutService.contentCenter;

    public constructor(
        private readonly translateService: TranslateService,
        private readonly matIconRegistry: MatIconRegistry,
    ) {
        translateService.addLangs(['en', 'cs']);
        translateService.use(localStorage.getItem('currentLanguage') ?? 'en');
        translateService.setDefaultLang('en');

        matIconRegistry.setDefaultFontSetClass('material-symbols-outlined');
    }
}
