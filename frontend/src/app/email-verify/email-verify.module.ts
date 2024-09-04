import { CommonModule } from '@angular/common';
import { NgModule } from '@angular/core';
import { ReactiveFormsModule } from '@angular/forms';
import { LayoutComponent } from '@app/email-verify/components/layout/layout.component';
import { VerifyComponent } from '@app/email-verify/components/verify/verify.component';
import {TranslateModule} from "@ngx-translate/core";

import { EmailVerifyRoutingModule } from './email-verify-routing.module';

@NgModule({
    imports: [
        CommonModule,
        ReactiveFormsModule,
        EmailVerifyRoutingModule,
        TranslateModule,
    ],
    declarations: [
        LayoutComponent,
        VerifyComponent,
    ]
})
export class EmailVerifyModule { }
