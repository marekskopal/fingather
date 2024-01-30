import { CommonModule } from '@angular/common';
import { NgModule } from '@angular/core';
import { ReactiveFormsModule } from '@angular/forms';
import { AddEditComponent } from '@app/portfolios/components/add-edit/add-edit.component';
import { LayoutComponent } from '@app/portfolios/components/layout/layout.component';
import { ListComponent } from '@app/portfolios/components/list/list.component';
import { FaIconComponent, FaIconLibrary } from '@fortawesome/angular-fontawesome';
import { faEdit, faPlus, faTrash } from '@fortawesome/free-solid-svg-icons';

import { PortfolioRoutingModule } from './portfolio-routing.module';

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
        faIconLibrary.addIcons(faPlus, faEdit, faTrash);
    }
}
