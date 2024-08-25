import { CommonModule } from '@angular/common';
import { NgModule } from '@angular/core';
import { FormsModule, ReactiveFormsModule } from '@angular/forms';
import {MatIcon} from "@angular/material/icon";
import { AddAssetComponent } from '@app/assets/components/add-asset/add-asset.component';
import {AssetChartsComponent} from "@app/assets/components/detail/components/asset-charts/asset-charts.component";
import {
    AssetTickerChartComponent
    // eslint-disable-next-line max-len
} from "@app/assets/components/detail/components/asset-charts/components/asset-ticker-chart/asset-ticker-chart.component";
import {
    AssetValueChartComponent
} from "@app/assets/components/detail/components/asset-charts/components/asset-value-chart/asset-value-chart.component";
import {AssetValueComponent} from "@app/assets/components/detail/components/asset-value/asset-value.component";
import {DividendListComponent} from "@app/assets/components/detail/components/dividends/dividend-list.component";
import {
    FundamentalRowComponent
} from "@app/assets/components/detail/components/funtamentals/components/fundamental-row/fundamental-row.component";
import {FundamentalsComponent} from "@app/assets/components/detail/components/funtamentals/fundamentals.component";
import {
    TransactionListComponent
} from "@app/assets/components/detail/components/transactions/transaction-list.component";
import { DetailComponent } from '@app/assets/components/detail/detail.component';
import { LayoutComponent } from '@app/assets/components/layout/layout.component';
import {OpenedAssetsComponent} from "@app/assets/components/list/components/opened-assets/opened-assets.component";
import {
    OpenedGroupedAssetsComponent
} from "@app/assets/components/list/components/opened-grouped-assets/opened-grouped-assets.component";
import { ListComponent } from '@app/assets/components/list/list.component';
import {ColoredValueDirective} from "@app/shared/directives/colored-value.directive";
import { NgbNavModule, NgbTypeaheadModule } from '@ng-bootstrap/ng-bootstrap';
import { NgApexchartsModule } from 'ng-apexcharts';

import { SharedModule } from '../shared/shared.module';
import { AssetsRoutingModule } from './assets-routing.module';


@NgModule({
    imports: [
        CommonModule,
        ReactiveFormsModule,
        SharedModule,
        AssetsRoutingModule,
        NgApexchartsModule,
        NgbTypeaheadModule,
        FormsModule,
        NgbNavModule,
        MatIcon,
        ColoredValueDirective,
    ],
    declarations: [
        LayoutComponent,
        ListComponent,
        AddAssetComponent,
        DetailComponent,
        TransactionListComponent,
        DividendListComponent,
        AssetChartsComponent,
        AssetTickerChartComponent,
        FundamentalsComponent,
        FundamentalRowComponent,
        AssetValueChartComponent,
        OpenedGroupedAssetsComponent,
        OpenedAssetsComponent,
        AssetValueComponent,
    ]
})
export class AssetsModule {
}
