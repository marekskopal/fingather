import { CommonModule } from '@angular/common';
import { NgModule } from '@angular/core';
import { ReactiveFormsModule } from '@angular/forms';
import {MatIcon} from "@angular/material/icon";
import {RouterModule} from "@angular/router";
import { ConfirmDialogComponent } from '@app/shared/components/confirm-dialog/confirm-dialog.component';
import {DateInputComponent} from "@app/shared/components/date-input/date-input.component";
import { DeleteButtonComponent } from '@app/shared/components/delete-button/delete-button.component';
import {
    ImportPrepareComponent
} from '@app/shared/components/import/components/import-prepare/import-prepare.component';
import { ImportComponent } from '@app/shared/components/import/import.component';
import {LanguageSelectorComponent} from "@app/shared/components/language-selector/language-selector.component";
import {NavigationComponent} from "@app/shared/components/navigation/navigation.component";
import { PaginationComponent } from '@app/shared/components/pagination/pagination.component';
import { PortfolioSelectorComponent } from '@app/shared/components/portfolio-selector/portfolio-selector.component';
import {
    PortfolioValueChartComponent
} from '@app/shared/components/portfolio-value-chart/portfolio-value-chart.component';
import {SearchInputComponent} from "@app/shared/components/search-input/search-input.component";
import {SelectComponent} from "@app/shared/components/select/select.component";
import { TableValueComponent } from '@app/shared/components/table-value/table-value.component';
import {TagComponent} from "@app/shared/components/tag/tag.component";
import { TickerLogoComponent } from '@app/shared/components/ticker-logo/ticker-logo.component';
import {TypeSelectComponent} from "@app/shared/components/type-select/type-select.component";
import { ValueColorComponent } from '@app/shared/components/value-color/value-color.component';
import {ColoredValueDirective} from "@app/shared/directives/colored-value.directive";
import { CurrencyPipe } from '@app/shared/pipes/currency.pipe';
import {NgbModule} from "@ng-bootstrap/ng-bootstrap";
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
        DateInputComponent,
        SelectComponent,
        TypeSelectComponent,
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
        DateInputComponent,
        SelectComponent,
        TypeSelectComponent,
    ]
})
export class SharedModule { }
