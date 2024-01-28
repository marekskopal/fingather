import { NgModule } from '@angular/core';
import { ReactiveFormsModule } from '@angular/forms';
import { CommonModule } from '@angular/common';

import { PortfolioRoutingModule } from './portfolio-routing.module';
import {FaIconComponent, FaIconLibrary} from "@fortawesome/angular-fontawesome";
import {faEdit, faPlus, faTrash} from "@fortawesome/free-solid-svg-icons";
import {LayoutComponent} from "@app/portfolios/components/layout/layout.component";
import {ListComponent} from "@app/portfolios/components/list/list.component";
import {AddEditComponent} from "@app/portfolios/components/add-edit/add-edit.component";

@NgModule({
    imports: [
        CommonModule,
        ReactiveFormsModule,
        PortfolioRoutingModule,
        FaIconComponent
    ],
    declarations: [
        LayoutComponent,
        ListComponent,
        AddEditComponent
    ]
})
export class PortfoliosModule {
    public constructor(
        private readonly faIconLibrary: FaIconLibrary
    ) {
        faIconLibrary.addIcons(faPlus, faEdit, faTrash)
    }
}
