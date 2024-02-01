import { CommonModule } from '@angular/common';
import { NgModule } from '@angular/core';
import { ReactiveFormsModule } from '@angular/forms';
import { ImportComponent } from '@app/assets/import/components/import/import.component';
import { LayoutComponent } from '@app/assets/import/components/layout/layout.component';

import { ImportRoutingModule } from './import-routing.module';

@NgModule({
    imports: [
        CommonModule,
        ReactiveFormsModule,
        ImportRoutingModule
    ],
    declarations: [
        LayoutComponent,
        ImportComponent
    ]
})
export class ImportModule { }
