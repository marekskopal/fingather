import { NgModule } from '@angular/core';
import {FormsModule, ReactiveFormsModule} from '@angular/forms';
import { CommonModule } from '@angular/common';
import { MomentModule } from 'ngx-moment';
import { NgApexchartsModule } from "ng-apexcharts";

import { AssetsRoutingModule } from './assets-routing.module';
import { LayoutComponent } from './components/layout.component';
import { ListComponent } from './components/list.component';
import { DetailComponent } from './components/detail.component';
import { TransactionListComponent } from './components/transactions/transaction-list.component';
import { TransactionDialogComponent } from './components/transactions/transaction-dialog.component';
import { DividendListComponent } from './components/dividends/dividend-list.component';
import { DividendDialogComponent } from './components/dividends/dividend-dialog.component';
import { AssetTickerChartComponent } from './components/chart/asset-ticker-chart.component';
import { SharedModule } from '../shared/shared.module';
import {AddAssetComponent} from "@app/assets/components/add-asset/add-asset.component";
import {NgbNavModule, NgbTypeaheadModule} from "@ng-bootstrap/ng-bootstrap";

@NgModule({
    imports: [
        CommonModule,
        ReactiveFormsModule,
        SharedModule,
        AssetsRoutingModule,
        MomentModule,
        NgApexchartsModule,
        NgbTypeaheadModule,
        FormsModule,
        NgbNavModule,
    ],
    declarations: [
        LayoutComponent,
        ListComponent,
        AddAssetComponent,
        DetailComponent,
        TransactionListComponent,
        TransactionDialogComponent,
        DividendListComponent,
        DividendDialogComponent,
        AssetTickerChartComponent,
    ]
})
export class AssetsModule { }
