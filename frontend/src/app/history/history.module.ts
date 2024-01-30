import { CommonModule } from '@angular/common';
import { NgModule } from '@angular/core';
import { ReactiveFormsModule } from '@angular/forms';
import { HistoryComponent } from '@app/history/components/history/history.component';
import { LayoutComponent } from '@app/history/components/layout/layout.component';
import {
    PortfolioValueChartComponent
} from '@app/history/components/portfolio-value-chart/portfolio-value-chart.component';
import { SharedModule } from '@app/shared/shared.module';
import { NgApexchartsModule } from 'ng-apexcharts';

import { HistoryRoutingModule } from './history-routing.module';

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
