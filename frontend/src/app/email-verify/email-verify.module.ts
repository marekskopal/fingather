import { NgModule } from '@angular/core';
import { ReactiveFormsModule } from '@angular/forms';
import { CommonModule } from '@angular/common';

import { EmailVerifyRoutingModule } from './email-verify-routing.module';
import { SharedModule } from '../shared/shared.module';
import {LayoutComponent} from "@app/email-verify/componenets/layout/layout.component";
import {VerifyComponent} from "@app/email-verify/componenets/verify/verify.component";

@NgModule({
    imports: [
        CommonModule,
        ReactiveFormsModule,
        SharedModule,
        EmailVerifyRoutingModule,
    ],
    declarations: [
        LayoutComponent,
        VerifyComponent,
    ]
})
export class EmailVerifyModule { }
