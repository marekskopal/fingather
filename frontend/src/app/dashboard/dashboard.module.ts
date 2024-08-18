import { CommonModule } from '@angular/common';
import { NgModule } from '@angular/core';
import { ReactiveFormsModule } from '@angular/forms';
import { DashboardComponent } from '@app/dashboard/components/dashboard/dashboard.component';
import { GroupChartComponent } from '@app/dashboard/components/group-chart/group-chart.component';
import { LayoutComponent } from '@app/dashboard/components/layout/layout.component';
import { NgApexchartsModule } from 'ng-apexcharts';

import { SharedModule } from '../shared/shared.module';
import { DashboardRoutingModule } from './dashboard-routing.module';
import {ColoredValueDirective} from "@app/shared/directives/colored-value.directive";
import {MatIcon} from "@angular/material/icon";

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
