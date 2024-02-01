import { CommonModule } from '@angular/common';
import { NgModule } from '@angular/core';
import { ReactiveFormsModule } from '@angular/forms';
import { LayoutComponent } from '@app/email-verify/components/layout/layout.component';
import { VerifyComponent } from '@app/email-verify/components/verify/verify.component';

import { SharedModule } from '../shared/shared.module';
import { EmailVerifyRoutingModule } from './email-verify-routing.module';

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
