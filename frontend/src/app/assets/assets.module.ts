import { CommonModule } from '@angular/common';
import { NgModule } from '@angular/core';
import { FormsModule, ReactiveFormsModule } from '@angular/forms';
import { AddAssetComponent } from '@app/assets/components/add-asset/add-asset.component';
import { AssetValueChartComponent } from '@app/assets/components/asset-value-chart/asset-value-chart.component';
import { DetailComponent } from '@app/assets/components/detail/detail.component';
import {
    FundamentalRowComponent
} from '@app/assets/components/funtamentals/components/fundamental-row/fundamental-row.component';
import { FundamentalsComponent } from '@app/assets/components/funtamentals/fundamentals.component';
import { LayoutComponent } from '@app/assets/components/layout/layout.component';
import { ListComponent } from '@app/assets/components/list/list.component';
import { NgbNavModule, NgbTypeaheadModule } from '@ng-bootstrap/ng-bootstrap';
import { NgApexchartsModule } from 'ng-apexcharts';

import { SharedModule } from '../shared/shared.module';
import { AssetsRoutingModule } from './assets-routing.module';
import { AssetTickerChartComponent } from './components/chart/asset-ticker-chart.component';
import { DividendListComponent } from './components/dividends/dividend-list.component';
import { TransactionListComponent } from './components/transactions/transaction-list.component';
import {MatIcon} from "@angular/material/icon";
import {
    OpenedGroupedAssetsComponent
} from "@app/assets/components/list/components/opened-grouped-assets/opened-grouped-assets.component";
import {ColoredValueDirective} from "@app/shared/directives/colored-value.directive";
import {OpenedAssetsComponent} from "@app/assets/components/list/components/opened-assets/opened-assets.component";

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
        AssetTickerChartComponent,
        FundamentalsComponent,
        FundamentalRowComponent,
        AssetValueChartComponent,
        OpenedGroupedAssetsComponent,
        OpenedAssetsComponent,
    ]
})
export class AssetsModule {
}
