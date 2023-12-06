import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { PortfolioTotalComponent } from "./components/pottfolio-total.component";

@NgModule({
    imports: [
        CommonModule,
    ],
    declarations: [
        PortfolioTotalComponent
    ],
    exports: [
        PortfolioTotalComponent
    ]
})
export class SharedModule { }
