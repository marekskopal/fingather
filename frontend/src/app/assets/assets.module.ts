import { CommonModule } from '@angular/common';
import { NgModule } from '@angular/core';
import { FormsModule, ReactiveFormsModule } from '@angular/forms';
import { AddAssetComponent } from '@app/assets/components/add-asset/add-asset.component';
import { DetailComponent } from '@app/assets/components/detail/detail.component';
import { LayoutComponent } from '@app/assets/components/layout/layout.component';
import { ListComponent } from '@app/assets/components/list/list.component';
import { FaIconLibrary, FontAwesomeModule } from '@fortawesome/angular-fontawesome';
import {
    faEdit, faExpand, faPlus, faTrash
} from '@fortawesome/free-solid-svg-icons';
import { NgbNavModule, NgbTypeaheadModule } from '@ng-bootstrap/ng-bootstrap';
import { NgApexchartsModule } from 'ng-apexcharts';
import { MomentModule } from 'ngx-moment';

import { SharedModule } from '../shared/shared.module';
import { AssetsRoutingModule } from './assets-routing.module';
import { AssetTickerChartComponent } from './components/chart/asset-ticker-chart.component';
import { DividendListComponent } from './components/dividends/dividend-list.component';
import { TransactionListComponent } from './components/transactions/transaction-list.component';

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
    public constructor(
        private readonly faIconLibrary: FaIconLibrary
    ) {
        faIconLibrary.addIcons(faPlus, faEdit, faTrash, faExpand);
    }
}
