import { NgModule } from '@angular/core';
import { ReactiveFormsModule } from '@angular/forms';
import { CommonModule } from '@angular/common';

import { PortfolioRoutingModule } from './portfolio-routing.module';
import { LayoutComponent } from './layout.component';
import { ListComponent } from './list.component';
import { SharedModule } from '../shared/shared.module';

@NgModule({
    imports: [
        CommonModule,
        ReactiveFormsModule,
        SharedModule,
        PortfolioRoutingModule,
    ],
    declarations: [
        LayoutComponent,
        ListComponent,
    ]
})
export class PortfolioModule { }
