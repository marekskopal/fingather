import { HTTP_INTERCEPTORS, HttpClient, HttpClientModule } from '@angular/common/http';
import { NgModule } from '@angular/core';
import { ReactiveFormsModule } from '@angular/forms';
import { BrowserModule } from '@angular/platform-browser';
import { ErrorInterceptor } from '@app/core/interceptors/error.interceptor';
import { JwtInterceptor } from '@app/core/interceptors/jwt.interceptor';
import { AlertComponent } from '@app/shared/components/alert/alert.component';
import { FaIconComponent, FaIconLibrary } from '@fortawesome/angular-fontawesome';
import {
    faChartLine, faChartPie, faDashboard, faEye,
    faHandshake, faLayerGroup, faList, faPowerOff,
    faUserGroup, faWallet
} from '@fortawesome/free-solid-svg-icons';
import { NgbModule } from '@ng-bootstrap/ng-bootstrap';
import { TranslateLoader, TranslateModule, TranslateService } from '@ngx-translate/core';
import { TranslateHttpLoader } from '@ngx-translate/http-loader';

import { AppComponent } from './app.component';
import { AppRoutingModule } from './app-routing.module';

export function HttpLoaderFactory(http: HttpClient): TranslateHttpLoader {
    return new TranslateHttpLoader(http, '/i18n/');
}

@NgModule({
    imports: [
        BrowserModule,
        ReactiveFormsModule,
        HttpClientModule,
        AppRoutingModule,
        NgbModule,
        FaIconComponent,
        TranslateModule.forRoot({
            loader: {
                provide: TranslateLoader,
                useFactory: HttpLoaderFactory,
                deps: [HttpClient]
            }
        })
    ],
    declarations: [
        AppComponent,
        AlertComponent,
    ],
    providers: [
        { provide: HTTP_INTERCEPTORS, useClass: JwtInterceptor, multi: true },
        { provide: HTTP_INTERCEPTORS, useClass: ErrorInterceptor, multi: true },
    ],
    bootstrap: [AppComponent]
})
export class AppModule {
    public constructor(
        private readonly faIconLibrary: FaIconLibrary,
        private readonly translateService: TranslateService,
    ) {
        faIconLibrary.addIcons(
            faDashboard,
            faChartPie,
            faList,
            faEye,
            faChartLine,
            faLayerGroup,
            faHandshake,
            faWallet,
            faUserGroup,
            faPowerOff
        );

        translateService.addLangs(['en', 'cs']);
        translateService.use(localStorage.getItem('currentLanguage') ?? 'en');
        translateService.setDefaultLang('en');
    }
}
