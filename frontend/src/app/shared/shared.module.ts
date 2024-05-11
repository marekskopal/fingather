import { CommonModule } from '@angular/common';
import { NgModule } from '@angular/core';
import { ReactiveFormsModule } from '@angular/forms';
import { ConfirmDialogComponent } from '@app/shared/components/confirm-dialog/confirm-dialog.component';
import { DividendDialogComponent } from '@app/shared/components/dividend-dialog/dividend-dialog.component';
import {
    ImportPrepareComponent
} from '@app/shared/components/import/components/import-prepare/import-prepare.component';
import { ImportComponent } from '@app/shared/components/import/import.component';
import { PortfolioSelectorComponent } from '@app/shared/components/portfolio-selector/portfolio-selector.component';
import {
    PortfolioValueChartComponent
} from '@app/shared/components/portfolio-value-chart/portfolio-value-chart.component';
import { TableValueComponent } from '@app/shared/components/table-value/table-value.component';
import { TickerLogoComponent } from '@app/shared/components/ticker-logo/ticker-logo.component';
import { TransactionDialogComponent } from '@app/shared/components/transaction-dialog/transaction-dialog.component';
import { ValueColorComponent } from '@app/shared/components/value-color/value-color.component';
import { CurrencyPipe } from '@app/shared/pipes/currency.pipe';
import { TranslateModule } from '@ngx-translate/core';
import { NgApexchartsModule } from 'ng-apexcharts';
import { NgxFileDropModule } from 'ngx-file-drop';

import { PortfolioTotalComponent } from './components/portfolio-total/portfolio-total.component';

@NgModule({
    imports: [
        CommonModule,
        ReactiveFormsModule,
        TranslateModule,
        NgApexchartsModule,
        NgxFileDropModule,
    ],
    declarations: [
        PortfolioTotalComponent,
        PortfolioSelectorComponent,
        CurrencyPipe,
        TransactionDialogComponent,
        DividendDialogComponent,
        ConfirmDialogComponent,
        TickerLogoComponent,
        ValueColorComponent,
        TableValueComponent,
        PortfolioValueChartComponent,
        ImportComponent,
        ImportPrepareComponent,
    ],
    exports: [
        PortfolioTotalComponent,
        PortfolioSelectorComponent,
        CurrencyPipe,
        TranslateModule,
        TickerLogoComponent,
        ValueColorComponent,
        TableValueComponent,
        PortfolioValueChartComponent,
        ImportComponent,
        ImportPrepareComponent,
    ]
})
export class SharedModule { }
