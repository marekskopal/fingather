import { CommonModule } from '@angular/common';
import { NgModule } from '@angular/core';
import { ReactiveFormsModule } from '@angular/forms';
import { HistoryComponent } from '@app/history/components/history/history.component';
import { LayoutComponent } from '@app/history/components/layout/layout.component';
import {AssetSelectorComponent} from "@app/shared/components/asset-selector/asset-selector.component";
import {LegendComponent} from "@app/shared/components/legend/legend.component";
import {PortfolioSelectorComponent} from "@app/shared/components/portfolio-selector/portfolio-selector.component";
import {
    PortfolioValueChartComponent
} from "@app/shared/components/portfolio-value-chart/portfolio-value-chart.component";
import {TranslateModule} from "@ngx-translate/core";

import { HistoryRoutingModule } from './history-routing.module';

@NgModule({
    imports: [
        CommonModule,
        ReactiveFormsModule,
        HistoryRoutingModule,
        PortfolioSelectorComponent,
        TranslateModule,
        AssetSelectorComponent,
        LegendComponent,
        PortfolioValueChartComponent,
    ],
    declarations: [
        LayoutComponent,
        HistoryComponent,
    ]
})
export class HistoryModule {
}
