import { Component } from '@angular/core';
import { PortfolioDataRangeEnum } from '@app/models';

@Component({ templateUrl: 'dividends-history.component.html' })
export class DividendsHistoryComponent {
    public range: PortfolioDataRangeEnum = PortfolioDataRangeEnum.All;

    public changeRange(range: PortfolioDataRangeEnum): void {
        this.range = range;
    }

    protected readonly PortfolioDataRangeEnum = PortfolioDataRangeEnum;
}
