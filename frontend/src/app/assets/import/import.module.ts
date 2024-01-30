import { CommonModule } from '@angular/common';
import { NgModule } from '@angular/core';
import { ReactiveFormsModule } from '@angular/forms';

import { ImportComponent } from './import.component';
import { ImportRoutingModule } from './import-routing.module';
import { LayoutComponent } from './layout.component';

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
