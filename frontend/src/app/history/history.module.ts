import { NgModule } from '@angular/core';
import { ReactiveFormsModule } from '@angular/forms';
import { CommonModule } from '@angular/common';

import { HistoryRoutingModule } from './history-routing.module';

import {LayoutComponent} from "@app/history/components/layout/layout.component";
import {HistoryComponent} from "@app/history/components/history/history.component";
import {
    PortfolioValueChartComponent
} from "@app/history/components/portfolio-value-chart/portfolio-value-chart.component";
import {NgApexchartsModule} from "ng-apexcharts";
import {SharedModule} from "@app/shared/shared.module";

@NgModule({
    imports: [
        CommonModule,
        ReactiveFormsModule,
        HistoryRoutingModule,
        NgApexchartsModule,
        SharedModule,
    ],
    declarations: [
        LayoutComponent,
        HistoryComponent,
        PortfolioValueChartComponent,
    ]
})
export class HistoryModule {
}
