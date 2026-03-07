import {provideHttpClient, withInterceptors} from "@angular/common/http";
import {enableProdMode, provideZonelessChangeDetection} from '@angular/core';
import {bootstrapApplication} from "@angular/platform-browser";
import {provideRouter} from "@angular/router";
import {AppComponent} from "@app/app.component";
import {appRoutes} from "@app/app-routes";
import {jwtInterceptor} from "@app/core/interceptors/jwt.interceptor";
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
            loader: provideTranslateHttpLoader({prefix: "/i18n/", suffix: `.json?v=${environment.i18nVersion}`}),
        }),
        provideHttpClient(withInterceptors([jwtInterceptor])),
        provideZonelessChangeDetection(),
    ],
});
