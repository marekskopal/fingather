import {
    HTTP_INTERCEPTORS, HttpClient, provideHttpClient, withInterceptorsFromDi
} from '@angular/common/http';
import { NgModule } from '@angular/core';
import { ReactiveFormsModule } from '@angular/forms';
import {MatIconRegistry} from "@angular/material/icon";
import { BrowserModule } from '@angular/platform-browser';
import { JwtInterceptor } from '@app/core/interceptors/jwt.interceptor';
import { AlertComponent } from '@app/shared/components/alert/alert.component';
import {SharedModule} from "@app/shared/shared.module";
import { NgbModule } from '@ng-bootstrap/ng-bootstrap';
import { TranslateLoader, TranslateModule, TranslateService } from '@ngx-translate/core';
import { TranslateHttpLoader } from '@ngx-translate/http-loader';

import { AppComponent } from './app.component';
import { AppRoutingModule } from './app-routing.module';

export function HttpLoaderFactory(http: HttpClient): TranslateHttpLoader {
    return new TranslateHttpLoader(http, '/i18n/');
}

@NgModule({
    declarations: [
        AppComponent,
        AlertComponent,
    ],
    bootstrap: [AppComponent],
    imports: [
        BrowserModule,
        ReactiveFormsModule,
        AppRoutingModule,
        NgbModule,
        SharedModule,
        TranslateModule.forRoot({
            loader: {
                provide: TranslateLoader,
                useFactory: HttpLoaderFactory,
                deps: [HttpClient]
            }
        })
    ],
    providers: [
        { provide: HTTP_INTERCEPTORS, useClass: JwtInterceptor, multi: true },
        provideHttpClient(withInterceptorsFromDi()),
    ]
})
export class AppModule {
    public constructor(
        private readonly translateService: TranslateService,
        private readonly matIconRegistry: MatIconRegistry
    ) {
        translateService.addLangs(['en', 'cs']);
        translateService.use(localStorage.getItem('currentLanguage') ?? 'en');
        translateService.setDefaultLang('en');

        matIconRegistry.setDefaultFontSetClass('material-symbols-outlined');
    }
}
