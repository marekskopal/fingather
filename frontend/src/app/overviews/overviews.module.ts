import { CommonModule } from '@angular/common';
import { NgModule } from '@angular/core';
import {MatIcon} from "@angular/material/icon";
import { LayoutComponent } from '@app/overviews/components/layout/layout.component';
import { ListComponent } from '@app/overviews/components/list/list.component';
import { OverviewsRoutingModule } from '@app/overviews/overviews-routing.module';
import {PortfolioSelectorComponent} from "@app/shared/components/portfolio-selector/portfolio-selector.component";
import {TableValueComponent} from "@app/shared/components/table-value/table-value.component";
import {CurrencyPipe} from "@app/shared/pipes/currency.pipe";
import {TranslateModule} from "@ngx-translate/core";

@NgModule({
    declarations: [
        LayoutComponent,
        ListComponent,
    ],
    imports: [
        CommonModule,
        OverviewsRoutingModule,
        MatIcon,
        PortfolioSelectorComponent,
        TranslateModule,
        TableValueComponent,
        CurrencyPipe,
    ]
})
export class OverviewsModule { }
