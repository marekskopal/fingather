import { NgModule } from '@angular/core';
import { ReactiveFormsModule } from '@angular/forms';
import { CommonModule } from '@angular/common';
import { MomentModule } from 'ngx-moment';

import { ImportRoutingModule } from './import-routing.module';
import { LayoutComponent } from './layout.component';
import { ImportComponent } from './import.component';

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
