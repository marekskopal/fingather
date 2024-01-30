import { CommonModule } from '@angular/common';
import { NgModule } from '@angular/core';
import { ReactiveFormsModule } from '@angular/forms';

import { AuthenticationRoutingModule } from './authentication-routing.module';
import { LayoutComponent } from './layout.component';
import { LoginComponent } from './login.component';
import { SignUpComponent } from './sign-up.component';

@NgModule({
    imports: [
        CommonModule,
        ReactiveFormsModule,
        AuthenticationRoutingModule
    ],
    declarations: [
        LayoutComponent,
        LoginComponent,
        SignUpComponent
    ]
})
export class AuthenticationModule { }
