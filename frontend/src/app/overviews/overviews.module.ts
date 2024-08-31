import { CommonModule } from '@angular/common';
import { NgModule } from '@angular/core';
import {MatIcon} from "@angular/material/icon";
import { LayoutComponent } from '@app/overviews/components/layout/layout.component';
import { ListComponent } from '@app/overviews/components/list/list.component';
import { OverviewsRoutingModule } from '@app/overviews/overviews-routing.module';
import { SharedModule } from '@app/shared/shared.module';

@NgModule({
    declarations: [
        LayoutComponent,
        ListComponent,
    ],
    imports: [
        CommonModule,
        OverviewsRoutingModule,
        SharedModule,
        MatIcon,
    ]
})
export class OverviewsModule { }
