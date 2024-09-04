import { CommonModule } from '@angular/common';
import { NgModule } from '@angular/core';
import { ReactiveFormsModule } from '@angular/forms';
import {MatIcon} from "@angular/material/icon";
import { AddEditGroupComponent } from '@app/groups/components/add-edit/add-edit-group.component';
import { LayoutComponent } from '@app/groups/components/layout/layout.component';
import { ListComponent } from '@app/groups/components/list/list.component';
import {DeleteButtonComponent} from "@app/shared/components/delete-button/delete-button.component";
import {InputValidatorComponent} from "@app/shared/components/input-validator/input-validator.component";
import {PortfolioSelectorComponent} from "@app/shared/components/portfolio-selector/portfolio-selector.component";
import {SaveButtonComponent} from "@app/shared/components/save-button/save-button.component";
import { NgbModule } from '@ng-bootstrap/ng-bootstrap';
import {TranslateModule} from "@ngx-translate/core";

import { GroupsRoutingModule } from './groups-routing.module';

@NgModule({
    imports: [
        CommonModule,
        ReactiveFormsModule,
        GroupsRoutingModule,
        NgbModule,
        MatIcon,
        InputValidatorComponent,
        SaveButtonComponent,
        PortfolioSelectorComponent,
        TranslateModule,
        DeleteButtonComponent,
    ],
    declarations: [
        LayoutComponent,
        ListComponent,
        AddEditGroupComponent
    ]
})
export class GroupsModule {
}
