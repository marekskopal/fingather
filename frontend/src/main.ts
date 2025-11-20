import {HTTP_INTERCEPTORS, provideHttpClient, withInterceptorsFromDi} from "@angular/common/http";
import {enableProdMode, provideZonelessChangeDetection} from '@angular/core';
import {bootstrapApplication} from "@angular/platform-browser";
import {provideRouter} from "@angular/router";
import {AppComponent} from "@app/app.component";
import {appRoutes} from "@app/app-routes";
import {JwtInterceptor} from "@app/core/interceptors/jwt.interceptor";
import { environment } from '@environments/environment';
import {provideTranslateService} from "@ngx-translate/core";
import {provideTranslateHttpLoader} from '@ngx-translate/http-loader';

if (environment.production) {
    enableProdMode();
}

bootstrapApplication(AppComponent, {
    providers: [
        provideRouter(appRoutes),
        provideTranslateService({
            loader: provideTranslateHttpLoader({prefix:"/i18n/", suffix:".json"}),
        }),
        {
            provide: HTTP_INTERCEPTORS,
            useClass: JwtInterceptor,
            multi: true,
        },
        provideHttpClient(withInterceptorsFromDi()),
        provideZonelessChangeDetection(),
    ],
});
