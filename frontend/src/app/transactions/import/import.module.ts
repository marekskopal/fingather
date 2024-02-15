import { CommonModule } from '@angular/common';
import { NgModule } from '@angular/core';
import { ReactiveFormsModule } from '@angular/forms';
import { ImportComponent } from '@app/transactions/import/components/import/import.component';
import { ImportPrepareComponent } from '@app/transactions/import/components/import-prepare/import-prepare.component';
import { LayoutComponent } from '@app/transactions/import/components/layout/layout.component';
import { TranslateModule } from '@ngx-translate/core';
import { NgxFileDropModule } from 'ngx-file-drop';

import { ImportRoutingModule } from './import-routing.module';

@NgModule({
    imports: [
        CommonModule,
        ReactiveFormsModule,
        ImportRoutingModule,
        TranslateModule,
        NgxFileDropModule,
    ],
    declarations: [
        LayoutComponent,
        ImportComponent,
        ImportPrepareComponent,
    ]
})
export class ImportModule { }
