import { CommonModule } from '@angular/common';
import { NgModule } from '@angular/core';
import { ReactiveFormsModule } from '@angular/forms';
import {MatIcon} from "@angular/material/icon";
import { AddEditComponent } from '@app/portfolios/components/add-edit/add-edit.component';
import { LayoutComponent } from '@app/portfolios/components/layout/layout.component';
import { ListComponent } from '@app/portfolios/components/list/list.component';
import { SharedModule } from '@app/shared/shared.module';

import { PortfolioRoutingModule } from './portfolio-routing.module';

@NgModule({
    imports: [
        CommonModule,
        ReactiveFormsModule,
        PortfolioRoutingModule,
        SharedModule,
        MatIcon,
    ],
    declarations: [
        LayoutComponent,
        ListComponent,
        AddEditComponent
    ]
})
export class PortfoliosModule {
}
