import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ListComponent } from "@app/overviews/components/list/list.component";
import {LayoutComponent} from "@app/overviews/components/layout/layout.component";
import {OverviewsRoutingModule} from "@app/overviews/overviews-routing.module";
import {SharedModule} from "@app/shared/shared.module";

@NgModule({
    declarations: [
        LayoutComponent,
        ListComponent,
    ],
    imports: [
        CommonModule,
        OverviewsRoutingModule,
        SharedModule,
    ]
})
export class OverviewsModule { }
