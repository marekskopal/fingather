import { HTTP_INTERCEPTORS,HttpClientModule } from '@angular/common/http';
import { NgModule } from '@angular/core';
import { ReactiveFormsModule } from '@angular/forms';
import { BrowserModule } from '@angular/platform-browser';
import {ErrorInterceptor} from '@app/core/interceptors/error.interceptor';
import {JwtInterceptor} from '@app/core/interceptors/jwt.interceptor';
import {AlertComponent} from '@app/shared/components/alert/alert.component';
import {FaIconComponent, FaIconLibrary} from '@fortawesome/angular-fontawesome';
import {
    faChartLine, faChartPie, faDashboard, faEye,
    faHandshake, faLayerGroup, faList, faPowerOff,
    faUserGroup, faWallet
} from '@fortawesome/free-solid-svg-icons';
import { NgbModule } from '@ng-bootstrap/ng-bootstrap';

import { AppComponent } from './app.component';
import { AppRoutingModule } from './app-routing.module';

@NgModule({
    imports: [
        BrowserModule,
        ReactiveFormsModule,
        HttpClientModule,
        AppRoutingModule,
        NgbModule,
        FaIconComponent,
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
        private readonly faIconLibrary: FaIconLibrary
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
    }
}
