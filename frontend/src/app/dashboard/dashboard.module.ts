import { CommonModule } from '@angular/common';
import { NgModule } from '@angular/core';
import { ReactiveFormsModule } from '@angular/forms';
import {MatIcon} from "@angular/material/icon";
import { DashboardComponent } from '@app/dashboard/components/dashboard/dashboard.component';
import { GroupChartComponent } from '@app/dashboard/components/group-chart/group-chart.component';
import { LayoutComponent } from '@app/dashboard/components/layout/layout.component';
import {LegendComponent} from "@app/shared/components/legend/legend.component";
import {PortfolioSelectorComponent} from "@app/shared/components/portfolio-selector/portfolio-selector.component";
import {PortfolioTotalComponent} from "@app/shared/components/portfolio-total/portfolio-total.component";
import {ColoredValueDirective} from "@app/shared/directives/colored-value.directive";
import {CurrencyPipe} from "@app/shared/pipes/currency.pipe";
import {TranslateModule} from "@ngx-translate/core";
import { NgApexchartsModule } from 'ng-apexcharts';

import { DashboardRoutingModule } from './dashboard-routing.module';

@NgModule({
    imports: [
        CommonModule,
        ReactiveFormsModule,
        DashboardRoutingModule,
        NgApexchartsModule,
        ColoredValueDirective,
        MatIcon,
        LegendComponent,
        PortfolioSelectorComponent,
        PortfolioTotalComponent,
        TranslateModule,
        CurrencyPipe,
    ],
    declarations: [
        LayoutComponent,
        DashboardComponent,
        GroupChartComponent,
    ]
})
export class DashboardModule { }
