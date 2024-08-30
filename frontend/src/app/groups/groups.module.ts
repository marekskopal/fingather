import { CommonModule } from '@angular/common';
import { NgModule } from '@angular/core';
import { ReactiveFormsModule } from '@angular/forms';
import {MatIcon} from "@angular/material/icon";
import { AddEditGroupComponent } from '@app/groups/components/add-edit/add-edit-group.component';
import { LayoutComponent } from '@app/groups/components/layout/layout.component';
import { ListComponent } from '@app/groups/components/list/list.component';
import { SharedModule } from '@app/shared/shared.module';
import { NgbModule } from '@ng-bootstrap/ng-bootstrap';

import { GroupsRoutingModule } from './groups-routing.module';

@NgModule({
    imports: [
        CommonModule,
        ReactiveFormsModule,
        GroupsRoutingModule,
        SharedModule,
        NgbModule,
        MatIcon,
    ],
    declarations: [
        LayoutComponent,
        ListComponent,
        AddEditGroupComponent
    ]
})
export class GroupsModule {
}
