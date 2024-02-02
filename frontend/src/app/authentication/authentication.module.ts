import { CommonModule } from '@angular/common';
import { NgModule } from '@angular/core';
import { ReactiveFormsModule } from '@angular/forms';
import { LayoutComponent } from '@app/authentication/components/layout/layout.component';
import { LoginComponent } from '@app/authentication/components/login/login.component';
import { SignUpComponent } from '@app/authentication/components/sign-up/sign-up.component';

import { AuthenticationRoutingModule } from './authentication-routing.module';
import {SharedModule} from "@app/shared/shared.module";

@NgModule({
    imports: [
        CommonModule,
        ReactiveFormsModule,
        AuthenticationRoutingModule,
        SharedModule,
    ],
    declarations: [
        LayoutComponent,
        LoginComponent,
        SignUpComponent
    ]
})
export class AuthenticationModule { }
