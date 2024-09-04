import { CommonModule } from '@angular/common';
import { NgModule } from '@angular/core';
import { ReactiveFormsModule } from '@angular/forms';
import {MatIcon} from "@angular/material/icon";
import { AddEditPortfolioComponent } from '@app/portfolios/components/add-edit/add-edit-portfolio.component';
import { LayoutComponent } from '@app/portfolios/components/layout/layout.component';
import { ListComponent } from '@app/portfolios/components/list/list.component';
import {DeleteButtonComponent} from "@app/shared/components/delete-button/delete-button.component";
import {InputValidatorComponent} from "@app/shared/components/input-validator/input-validator.component";
import {PortfolioSelectorComponent} from "@app/shared/components/portfolio-selector/portfolio-selector.component";
import {SaveButtonComponent} from "@app/shared/components/save-button/save-button.component";
import {SelectComponent} from "@app/shared/components/select/select.component";
import {TranslateModule} from "@ngx-translate/core";

import { PortfolioRoutingModule } from './portfolio-routing.module';

@NgModule({
    imports: [
        CommonModule,
        ReactiveFormsModule,
        PortfolioRoutingModule,
        MatIcon,
        InputValidatorComponent,
        SaveButtonComponent,
        PortfolioSelectorComponent,
        TranslateModule,
        DeleteButtonComponent,
        SelectComponent,
    ],
    declarations: [
        LayoutComponent,
        ListComponent,
        AddEditPortfolioComponent
    ]
})
export class PortfoliosModule {
}
