import { ChangeDetectionStrategy, Component, signal } from '@angular/core';
import {
    DividendsDataChartComponent,
} from "@app/dividends/components/dividend-data-chart/dividends-data-chart.component";
import {
    DividendForecastCalendarComponent,
} from "@app/dividends/components/forecast-calendar/dividend-forecast-calendar.component";
import {TransactionActionType} from "@app/models";
import { RangeEnum } from '@app/models/enums/range-enum';
import {PortfolioSelectorComponent} from "@app/shared/components/portfolio-selector/portfolio-selector.component";
import {
    TransactionGridColumnEnum,
} from "@app/transactions/components/transaction-list/enums/transaction-grid-column-enum";
import {TransactionListComponent} from "@app/transactions/components/transaction-list/transaction-list.component";
import {ScrollShadowDirective} from "@marekskopal/ng-scroll-shadow";
import { TranslatePipe} from "@ngx-translate/core";

@Component({
    templateUrl: 'dividends-history.component.html',
    imports: [
        PortfolioSelectorComponent,
        TranslatePipe,
        DividendsDataChartComponent,
        ScrollShadowDirective,
        TransactionListComponent,
        DividendForecastCalendarComponent,
    ],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class DividendsHistoryComponent {
    protected readonly activeTab = signal<'history' | 'forecast'>('history');

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

    protected setTab(tab: 'history' | 'forecast'): void {
        this.activeTab.set(tab);
    }

    protected changeActiveRange(activeRange: RangeEnum): void {
        this.activeRange = activeRange;
    }

    protected readonly TransactionActionType = TransactionActionType;
    protected readonly TransactionGridColumnEnum = TransactionGridColumnEnum;
}
