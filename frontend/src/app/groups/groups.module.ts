import { CommonModule } from '@angular/common';
import { NgModule } from '@angular/core';
import { ReactiveFormsModule } from '@angular/forms';
import { AddEditComponent } from '@app/groups/components/add-edit/add-edit.component';
import { LayoutComponent } from '@app/groups/components/layout/layout.component';
import { ListComponent } from '@app/groups/components/list/list.component';
import { SharedModule } from '@app/shared/shared.module';
import { FaIconComponent, FaIconLibrary } from '@fortawesome/angular-fontawesome';
import { faEdit, faPlus, faTrash } from '@fortawesome/free-solid-svg-icons';

import { GroupsRoutingModule } from './groups-routing.module';

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
        faIconLibrary.addIcons(faPlus, faEdit, faTrash);
    }
}
