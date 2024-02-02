import { CommonModule } from '@angular/common';
import { NgModule } from '@angular/core';
import { ReactiveFormsModule } from '@angular/forms';
import { ImportComponent } from '@app/transactions/import/components/import/import.component';
import { LayoutComponent } from '@app/transactions/import/components/layout/layout.component';
import { TranslateModule } from '@ngx-translate/core';

import { ImportRoutingModule } from './import-routing.module';

@NgModule({
    imports: [
        CommonModule,
        ReactiveFormsModule,
        ImportRoutingModule,
        TranslateModule
    ],
    declarations: [
        LayoutComponent,
        ImportComponent
    ]
})
export class ImportModule { }
