import { CommonModule } from '@angular/common';
import { NgModule } from '@angular/core';
import { ReactiveFormsModule } from '@angular/forms';
import {SharedModule} from '@app/shared/shared.module';
import {FaIconComponent, FaIconLibrary} from '@fortawesome/angular-fontawesome';
import {faEdit, faPlus, faTrash} from '@fortawesome/free-solid-svg-icons';

import { AddEditComponent } from './add-edit.component';
import { BrokersRoutingModule } from './brokers-routing.module';
import { LayoutComponent } from './layout.component';
import { ListComponent } from './list.component';

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
        faIconLibrary.addIcons(faPlus, faEdit, faTrash)
    }
}
