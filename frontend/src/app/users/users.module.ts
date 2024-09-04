import { CommonModule } from '@angular/common';
import { NgModule } from '@angular/core';
import { ReactiveFormsModule } from '@angular/forms';
import {MatIcon} from "@angular/material/icon";
import {DeleteButtonComponent} from "@app/shared/components/delete-button/delete-button.component";
import {InputValidatorComponent} from "@app/shared/components/input-validator/input-validator.component";
import {PortfolioSelectorComponent} from "@app/shared/components/portfolio-selector/portfolio-selector.component";
import {SaveButtonComponent} from "@app/shared/components/save-button/save-button.component";
import {SelectComponent} from "@app/shared/components/select/select.component";
import { AddEditUserComponent } from '@app/users/add-edit/add-edit-user.component';
import { LayoutComponent } from '@app/users/layout/layout.component';
import { ListComponent } from '@app/users/list/list.component';
import {TranslateModule} from "@ngx-translate/core";

import { UsersRoutingModule } from './users-routing.module';

@NgModule({
    imports: [
        CommonModule,
        ReactiveFormsModule,
        UsersRoutingModule,
        MatIcon,
        SaveButtonComponent,
        InputValidatorComponent,
        DeleteButtonComponent,
        PortfolioSelectorComponent,
        TranslateModule,
        SelectComponent,
    ],
    declarations: [
        LayoutComponent,
        ListComponent,
        AddEditUserComponent
    ]
})
export class UsersModule {
}
