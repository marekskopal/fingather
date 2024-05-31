import { CommonModule } from '@angular/common';
import { NgModule } from '@angular/core';
import { ReactiveFormsModule } from '@angular/forms';
import {
    DividendsDataChartComponent
} from '@app/dividends/components/dividend-data-chart/dividends-data-chart.component';
import { DividendsHistoryComponent } from '@app/dividends/components/history/dividends-history.component';
import { LayoutComponent } from '@app/dividends/components/layout/layout.component';
import { SharedModule } from '@app/shared/shared.module';
import { NgApexchartsModule } from 'ng-apexcharts';

import { DividendsRoutingModule } from './dividends-routing.module';

@NgModule({
    imports: [
        CommonModule,
        ReactiveFormsModule,
        DividendsRoutingModule,
        NgApexchartsModule,
        SharedModule,
    ],
    declarations: [
        LayoutComponent,
        DividendsHistoryComponent,
        DividendsDataChartComponent,
    ]
})
export class DividendsModule {
}
