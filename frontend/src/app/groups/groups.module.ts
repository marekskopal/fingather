import { CommonModule } from '@angular/common';
import { NgModule } from '@angular/core';
import { ReactiveFormsModule } from '@angular/forms';
import {SharedModule} from '@app/shared/shared.module';
import {FaIconComponent, FaIconLibrary} from '@fortawesome/angular-fontawesome';
import {faEdit, faPlus, faTrash} from '@fortawesome/free-solid-svg-icons';

import { AddEditComponent } from './add-edit.component';
import { GroupsRoutingModule } from './groups-routing.module';
import { LayoutComponent } from './layout.component';
import { ListComponent } from './list.component';

@NgModule({
    imports: [
        CommonModule,
        ReactiveFormsModule,
        GroupsRoutingModule,
        FaIconComponent,
        SharedModule
    ],
    declarations: [
        LayoutComponent,
        ListComponent,
        AddEditComponent
    ]
})
export class GroupsModule {
    public constructor(
        private readonly faIconLibrary: FaIconLibrary
    ) {
        faIconLibrary.addIcons(faPlus, faEdit, faTrash)
    }
}
