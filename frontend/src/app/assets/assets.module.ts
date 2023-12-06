import { NgModule } from '@angular/core';
import { ReactiveFormsModule } from '@angular/forms';
import { CommonModule } from '@angular/common';
import { MomentModule } from 'ngx-moment';
import { NgApexchartsModule } from "ng-apexcharts";

import { AssetsRoutingModule } from './assets-routing.module';
import { LayoutComponent } from './components/layout.component';
import { ListComponent } from './components/list.component';
import { AddEditComponent } from './components/add-edit.component';
import { DetailComponent } from './components/detail.component';
import { TransactionListComponent } from './components/transactions/transaction-list.component';
import { TransactionDialogComponent } from './components/transactions/transaction-dialog.component';
import { DividendListComponent } from './components/dividends/dividend-list.component';
import { DividendDialogComponent } from './components/dividends/dividend-dialog.component';
import { AssetTickerChartComponent } from './components/chart/asset-ticker-chart.component';
import { SharedModule } from '../shared/shared.module';

@NgModule({
    imports: [
        CommonModule,
        ReactiveFormsModule,
        SharedModule,
        AssetsRoutingModule,
        MomentModule,
        NgApexchartsModule,
    ],
    declarations: [
        LayoutComponent,
        ListComponent,
        AddEditComponent,
        DetailComponent,
        TransactionListComponent,
        TransactionDialogComponent,
        DividendListComponent,
        DividendDialogComponent,
        AssetTickerChartComponent,
    ]
})
export class AssetsModule { }
