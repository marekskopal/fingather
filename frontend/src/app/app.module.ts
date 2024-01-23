import { NgModule } from '@angular/core';
import { BrowserModule } from '@angular/platform-browser';
import { ReactiveFormsModule } from '@angular/forms';
import { HttpClientModule, HTTP_INTERCEPTORS } from '@angular/common/http';
import { AppRoutingModule } from './app-routing.module';
import { AppComponent } from './app.component';
import { NgbModule } from '@ng-bootstrap/ng-bootstrap';
import {AlertComponent} from "@app/shared/components/alert/alert.component";
import {JwtInterceptor} from "@app/core/interceptors/jwt.interceptor";
import {ErrorInterceptor} from "@app/core/interceptors/error.interceptor";
import {FaIconComponent, FaIconLibrary} from "@fortawesome/angular-fontawesome";
import {
    faChartLine, faChartPie, faDashboard, faEye,
    faHandshake, faLayerGroup, faList, faPowerOff,
    faUserGroup
} from "@fortawesome/free-solid-svg-icons";

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
        faIconLibrary.addIcons(faDashboard, faChartPie, faList, faEye, faChartLine, faLayerGroup, faHandshake, faUserGroup, faPowerOff)
    }
}
