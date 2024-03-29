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
    ],
    exports: [
        PortfolioTotalComponent,
        PortfolioSelectorComponent,
        CurrencyPipe,
        TranslateModule,
        TickerLogoComponent,
    ]
})
export class SharedModule { }
