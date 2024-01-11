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
import { DividendListComponent } from './components/dividends/dividend-list.component';
import { AssetTickerChartComponent } from './components/chart/asset-ticker-chart.component';
import { SharedModule } from '../shared/shared.module';
import {AddAssetComponent} from "@app/assets/components/add-asset/add-asset.component";
import {NgbNavModule, NgbTypeaheadModule} from "@ng-bootstrap/ng-bootstrap";
import {FaIconLibrary, FontAwesomeModule} from "@fortawesome/angular-fontawesome";
import {faEdit, faExpand, faPlus, faTrash, faFileImport} from "@fortawesome/free-solid-svg-icons";

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
        FontAwesomeModule,
    ],
    declarations: [
        LayoutComponent,
        ListComponent,
        AddAssetComponent,
        DetailComponent,
        TransactionListComponent,
        DividendListComponent,
        AssetTickerChartComponent,
    ]
})
export class AssetsModule {
    constructor(
        private faIconLibrary: FaIconLibrary
    ) {
        faIconLibrary.addIcons(faPlus, faEdit, faTrash, faExpand, faFileImport)
    }
}
