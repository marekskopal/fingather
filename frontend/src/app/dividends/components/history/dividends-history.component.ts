import { ChangeDetectionStrategy, Component } from '@angular/core';
import {MatIcon} from "@angular/material/icon";
import {RouterLink} from "@angular/router";
import {
    DividendsDataChartComponent
} from "@app/dividends/components/dividend-data-chart/dividends-data-chart.component";
import {TransactionActionType} from "@app/models";
import { RangeEnum } from '@app/models/enums/range-enum';
import {PortfolioSelectorComponent} from "@app/shared/components/portfolio-selector/portfolio-selector.component";
import {
    TransactionGridColumnEnum
} from "@app/transactions/components/transaction-list/enums/transaction-grid-column-enum";
import {TransactionListComponent} from "@app/transactions/components/transaction-list/transaction-list.component";
import {ScrollShadowDirective} from "@marekskopal/ng-scroll-shadow";
import {TranslateModule} from "@ngx-translate/core";

@Component({
    templateUrl: 'dividends-history.component.html',
    standalone: true,
    imports: [
        PortfolioSelectorComponent,
        TranslateModule,
        DividendsDataChartComponent,
        MatIcon,
        RouterLink,
        ScrollShadowDirective,
        TransactionListComponent
    ],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class DividendsHistoryComponent {
    protected activeRange: RangeEnum = RangeEnum.YTD;

    protected ranges: {range: RangeEnum, text: string, number: number | null}[] = [
        {range: RangeEnum.SevenDays, text: 'app.history.history.d', number: 7},
        {range: RangeEnum.OneMonth, text: 'app.history.history.m', number: 1},
        {range: RangeEnum.ThreeMonths, text: 'app.history.history.m', number: 3},
        {range: RangeEnum.SixMonths, text: 'app.history.history.m', number: 6},
        {range: RangeEnum.YTD, text: 'app.history.history.ytd', number: null},
        {range: RangeEnum.OneYear, text: 'app.history.history.y', number: 1},
        {range: RangeEnum.All, text: 'app.history.history.all', number: null},
    ];

    protected changeActiveRange(activeRange: RangeEnum): void {
        this.activeRange = activeRange;
    }

    protected readonly TransactionActionType = TransactionActionType;
    protected readonly TransactionGridColumnEnum = TransactionGridColumnEnum;
}
