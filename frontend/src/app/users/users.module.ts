import { CommonModule } from '@angular/common';
import { NgModule } from '@angular/core';
import { ReactiveFormsModule } from '@angular/forms';
import {MatIcon} from "@angular/material/icon";
import {InputValidatorComponent} from "@app/shared/components/input-validator/input-validator.component";
import {SaveButtonComponent} from "@app/shared/components/save-button/save-button.component";
import { SharedModule } from '@app/shared/shared.module';
import { AddEditUserComponent } from '@app/users/add-edit/add-edit-user.component';
import { LayoutComponent } from '@app/users/layout/layout.component';
import { ListComponent } from '@app/users/list/list.component';

import { UsersRoutingModule } from './users-routing.module';

@NgModule({
    imports: [
        CommonModule,
        ReactiveFormsModule,
        UsersRoutingModule,
        SharedModule,
        MatIcon,
        SaveButtonComponent,
        InputValidatorComponent,
    ],
    declarations: [
        LayoutComponent,
        ListComponent,
        AddEditUserComponent
    ]
})
export class UsersModule {
}
