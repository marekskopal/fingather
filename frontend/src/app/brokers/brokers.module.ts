import { CommonModule } from '@angular/common';
import { NgModule } from '@angular/core';
import { ReactiveFormsModule } from '@angular/forms';
import { AddEditComponent } from '@app/brokers/components/add-edit/add-edit.component';
import { LayoutComponent } from '@app/brokers/components/layout/layout.component';
import { ListComponent } from '@app/brokers/components/list/list.component';
import { SharedModule } from '@app/shared/shared.module';
import { FaIconComponent, FaIconLibrary } from '@fortawesome/angular-fontawesome';
import { faEdit, faPlus, faTrash } from '@fortawesome/free-solid-svg-icons';

import { BrokersRoutingModule } from './brokers-routing.module';

@NgModule({
    imports: [
        CommonModule,
        ReactiveFormsModule,
        BrokersRoutingModule,
        FaIconComponent,
        SharedModule
    ],
    declarations: [
        LayoutComponent,
        ListComponent,
        AddEditComponent
    ]
})
export class BrokersModule {
    public constructor(
        private readonly faIconLibrary: FaIconLibrary
    ) {
        faIconLibrary.addIcons(faPlus, faEdit, faTrash);
    }
}
