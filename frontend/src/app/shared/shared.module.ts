import { CommonModule } from '@angular/common';
import { NgModule } from '@angular/core';
import { ReactiveFormsModule } from '@angular/forms';
import { ConfirmDialogComponent } from '@app/shared/components/confirm-dialog/confirm-dialog.component';
import { DeleteButtonComponent } from '@app/shared/components/delete-button/delete-button.component';
import {
    ImportPrepareComponent
} from '@app/shared/components/import/components/import-prepare/import-prepare.component';
import { ImportComponent } from '@app/shared/components/import/import.component';
import { PaginationComponent } from '@app/shared/components/pagination/pagination.component';
import { PortfolioSelectorComponent } from '@app/shared/components/portfolio-selector/portfolio-selector.component';
import {
    PortfolioValueChartComponent
} from '@app/shared/components/portfolio-value-chart/portfolio-value-chart.component';
import { TableValueComponent } from '@app/shared/components/table-value/table-value.component';
import { TickerLogoComponent } from '@app/shared/components/ticker-logo/ticker-logo.component';
import { ValueColorComponent } from '@app/shared/components/value-color/value-color.component';
import { CurrencyPipe } from '@app/shared/pipes/currency.pipe';
import { TranslateModule } from '@ngx-translate/core';
import { NgApexchartsModule } from 'ng-apexcharts';
import { NgxFileDropModule } from 'ngx-file-drop';

import { PortfolioTotalComponent } from './components/portfolio-total/portfolio-total.component';
import {LanguageSelectorComponent} from "@app/shared/components/language-selector/language-selector.component";
import {NavigationComponent} from "@app/shared/components/navigation/navigation.component";
import {NgbModule} from "@ng-bootstrap/ng-bootstrap";
import {RouterModule} from "@angular/router";
import {ColoredValueDirective} from "@app/shared/directives/colored-value.directive";
import {MatIcon} from "@angular/material/icon";
import {TagComponent} from "@app/shared/components/tag/tag.component";
import {SearchInputComponent} from "@app/shared/components/search-input/search-input.component";

@NgModule({
    imports: [
        CommonModule,
        ReactiveFormsModule,
        TranslateModule,
        NgApexchartsModule,
        NgxFileDropModule,
        NgbModule,
        RouterModule,
        ColoredValueDirective,
        MatIcon,
    ],
    declarations: [
        PortfolioTotalComponent,
        PortfolioSelectorComponent,
        CurrencyPipe,
        ConfirmDialogComponent,
        TickerLogoComponent,
        ValueColorComponent,
        TableValueComponent,
        PortfolioValueChartComponent,
        ImportComponent,
        ImportPrepareComponent,
        PaginationComponent,
        DeleteButtonComponent,
        LanguageSelectorComponent,
        NavigationComponent,
        TagComponent,
        SearchInputComponent,
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
        PaginationComponent,
        DeleteButtonComponent,
        NavigationComponent,
        TagComponent,
        SearchInputComponent,
    ]
})
export class SharedModule { }
