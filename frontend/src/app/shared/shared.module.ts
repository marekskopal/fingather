import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { PortfolioTotalComponent } from "./components/pottfolio-total.component";
import {CurrencyPipe} from "@app/shared/pipes/currency.pipe";

@NgModule({
    imports: [
        CommonModule,
    ],
    declarations: [
        PortfolioTotalComponent,
        CurrencyPipe,
    ],
    exports: [
        PortfolioTotalComponent,
        CurrencyPipe,
    ]
})
export class SharedModule { }
