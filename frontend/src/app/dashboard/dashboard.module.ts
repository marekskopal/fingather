import { CommonModule } from '@angular/common';
import { NgModule } from '@angular/core';
import { ReactiveFormsModule } from '@angular/forms';
import {MatIcon} from "@angular/material/icon";
import { DashboardComponent } from '@app/dashboard/components/dashboard/dashboard.component';
import { GroupChartComponent } from '@app/dashboard/components/group-chart/group-chart.component';
import { LayoutComponent } from '@app/dashboard/components/layout/layout.component';
import {ColoredValueDirective} from "@app/shared/directives/colored-value.directive";
import { NgApexchartsModule } from 'ng-apexcharts';

import { SharedModule } from '../shared/shared.module';
import { DashboardRoutingModule } from './dashboard-routing.module';

@NgModule({
    imports: [
        CommonModule,
        ReactiveFormsModule,
        SharedModule,
        DashboardRoutingModule,
        NgApexchartsModule,
        ColoredValueDirective,
        MatIcon,
    ],
    declarations: [
        LayoutComponent,
        DashboardComponent,
        GroupChartComponent,
    ]
})
export class DashboardModule { }
