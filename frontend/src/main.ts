import {HTTP_INTERCEPTORS, HttpClient, provideHttpClient, withInterceptorsFromDi} from "@angular/common/http";
import {enableProdMode, importProvidersFrom, provideExperimentalZonelessChangeDetection} from '@angular/core';
import {bootstrapApplication} from "@angular/platform-browser";
import {provideRouter} from "@angular/router";
import {AppComponent, HttpLoaderFactory} from "@app/app.component";
import {appRoutes} from "@app/app-routes";
import {JwtInterceptor} from "@app/core/interceptors/jwt.interceptor";
import { environment } from '@environments/environment';
import {TranslateLoader, TranslateModule} from "@ngx-translate/core";

if (environment.production) {
    enableProdMode();
}

bootstrapApplication(AppComponent, {
    providers: [
        provideRouter(appRoutes),
        importProvidersFrom(
            TranslateModule.forRoot({
                loader: {
                    provide: TranslateLoader,
                    useFactory: HttpLoaderFactory,
                    deps: [HttpClient]
                }
            })
        ),
        {
            provide: HTTP_INTERCEPTORS,
            useClass: JwtInterceptor,
            multi: true
        },
        provideHttpClient(withInterceptorsFromDi()),
        provideExperimentalZonelessChangeDetection(),
    ]
});
