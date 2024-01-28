import { NgModule } from '@angular/core';
import { ReactiveFormsModule } from '@angular/forms';
import { CommonModule } from '@angular/common';

import { DashboardRoutingModule } from './dashboard-routing.module';
import { SharedModule } from '../shared/shared.module';
import {LayoutComponent} from "@app/dashboard/components/layout/layout.component";
import {DashboardComponent} from "@app/dashboard/components/dashboard/dashboard.component";
import {GroupChartComponent} from "@app/dashboard/components/group-chart/group-chart.component";
import {NgApexchartsModule} from "ng-apexcharts";
import {FaIconComponent} from "@fortawesome/angular-fontawesome";

@NgModule({
    imports: [
        CommonModule,
        ReactiveFormsModule,
        SharedModule,
        DashboardRoutingModule,
        NgApexchartsModule,
        FaIconComponent,
    ],
    declarations: [
        LayoutComponent,
        DashboardComponent,
        GroupChartComponent,
    ]
})
export class DashboardModule { }
