import { ChangeDetectionStrategy, Component } from '@angular/core';
import { RangeEnum } from '@app/models/enums/range-enum';

@Component({
    templateUrl: 'dividends-history.component.html',
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class DividendsHistoryComponent {
    public range: RangeEnum = RangeEnum.YTD;

    public changeRange(range: RangeEnum): void {
        this.range = range;
    }

    protected readonly PortfolioDataRangeEnum = RangeEnum;
}
