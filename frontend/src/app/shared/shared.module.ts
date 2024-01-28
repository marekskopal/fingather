import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { PortfolioTotalComponent } from "./components/portfolio-total/portfolio-total.component";
import {CurrencyPipe} from "@app/shared/pipes/currency.pipe";
import {ReactiveFormsModule} from "@angular/forms";
import {TransactionDialogComponent} from "@app/shared/components/transaction-dialog/transaction-dialog.component";
import {DividendDialogComponent} from "@app/shared/components/dividend-dialog/dividend-dialog.component";
import {PortfolioSelectorComponent} from "@app/shared/components/portfolio-selector/pottfolio-selector.component";

@NgModule({
    imports: [
        CommonModule,
        ReactiveFormsModule,
    ],
    declarations: [
        PortfolioTotalComponent,
        PortfolioSelectorComponent,
        CurrencyPipe,
        TransactionDialogComponent,
        DividendDialogComponent,
    ],
    exports: [
        PortfolioTotalComponent,
        PortfolioSelectorComponent,
        CurrencyPipe,
    ]
})
export class SharedModule { }
