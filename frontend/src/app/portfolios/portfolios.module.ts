import { CommonModule } from '@angular/common';
import { NgModule } from '@angular/core';
import { ReactiveFormsModule } from '@angular/forms';
import {MatIcon} from "@angular/material/icon";
import { AddEditPortfolioComponent } from '@app/portfolios/components/add-edit/add-edit-portfolio.component';
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
        AddEditPortfolioComponent
    ]
})
export class PortfoliosModule {
}
