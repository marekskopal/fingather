import {CommonModule, NgOptimizedImage} from '@angular/common';
import { NgModule } from '@angular/core';
import { ReactiveFormsModule } from '@angular/forms';
import { LayoutComponent } from '@app/authentication/components/layout/layout.component';
import { LoginComponent } from '@app/authentication/components/login/login.component';
import { SignUpComponent } from '@app/authentication/components/sign-up/sign-up.component';
import {InputValidatorComponent} from "@app/shared/components/input-validator/input-validator.component";
import {SaveButtonComponent} from "@app/shared/components/save-button/save-button.component";
import { SharedModule } from '@app/shared/shared.module';

import { AuthenticationRoutingModule } from './authentication-routing.module';

@NgModule({
    imports: [
        CommonModule,
        ReactiveFormsModule,
        AuthenticationRoutingModule,
        SharedModule,
        InputValidatorComponent,
        SaveButtonComponent,
        NgOptimizedImage,
    ],
    declarations: [
        LayoutComponent,
        LoginComponent,
        SignUpComponent
    ]
})
export class AuthenticationModule { }
