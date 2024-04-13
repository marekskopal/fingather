import { CommonModule } from '@angular/common';
import { NgModule } from '@angular/core';
import { ReactiveFormsModule } from '@angular/forms';
import { ConfirmDialogComponent } from '@app/shared/components/confirm-dialog/confirm-dialog.component';
import { DividendDialogComponent } from '@app/shared/components/dividend-dialog/dividend-dialog.component';
import { PortfolioSelectorComponent } from '@app/shared/components/portfolio-selector/portfolio-selector.component';
import { TickerLogoComponent } from '@app/shared/components/ticker-logo/ticker-logo.component';
import { TransactionDialogComponent } from '@app/shared/components/transaction-dialog/transaction-dialog.component';
import { CurrencyPipe } from '@app/shared/pipes/currency.pipe';
import { TranslateModule } from '@ngx-translate/core';

import { PortfolioTotalComponent } from './components/portfolio-total/portfolio-total.component';
import {ValueColorComponent} from "@app/shared/components/value-color/value-color.component";
import {TableValueComponent} from "@app/shared/components/table-value/table-value.component";

@NgModule({
    imports: [
        CommonModule,
        ReactiveFormsModule,
        TranslateModule
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
    ],
    exports: [
        PortfolioTotalComponent,
        PortfolioSelectorComponent,
        CurrencyPipe,
        TranslateModule,
        TickerLogoComponent,
        ValueColorComponent,
        TableValueComponent,
    ]
})
export class SharedModule { }
