import { CommonModule } from '@angular/common';
import { NgModule } from '@angular/core';
import { ReactiveFormsModule } from '@angular/forms';
import {FaIconComponent, FaIconLibrary} from '@fortawesome/angular-fontawesome';
import {faEdit, faPlus, faTrash} from '@fortawesome/free-solid-svg-icons';

import { AddEditComponent } from './add-edit.component';
import { LayoutComponent } from './layout.component';
import { ListComponent } from './list.component';
import { UsersRoutingModule } from './users-routing.module';

@NgModule({
    imports: [
        CommonModule,
        ReactiveFormsModule,
        UsersRoutingModule,
        FaIconComponent
    ],
    declarations: [
        LayoutComponent,
        ListComponent,
        AddEditComponent
    ]
})
export class UsersModule {
    public constructor(
        private readonly faIconLibrary: FaIconLibrary
    ) {
        faIconLibrary.addIcons(faPlus, faEdit, faTrash)
    }
}
