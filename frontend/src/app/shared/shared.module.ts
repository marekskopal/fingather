import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { PortfolioTotalComponent } from "./components/pottfolio-total.component";
import {CurrencyPipe} from "@app/shared/pipes/currency.pipe";
import {ReactiveFormsModule} from "@angular/forms";
import {TransactionDialogComponent} from "@app/shared/components/transaction-dialog/transaction-dialog.component";
import {DividendDialogComponent} from "@app/shared/components/dividend-dialog/dividend-dialog.component";

@NgModule({
    imports: [
        CommonModule,
        ReactiveFormsModule,
    ],
    declarations: [
        PortfolioTotalComponent,
        CurrencyPipe,
        TransactionDialogComponent,
        DividendDialogComponent,
    ],
    exports: [
        PortfolioTotalComponent,
        CurrencyPipe,
    ]
})
export class SharedModule { }
