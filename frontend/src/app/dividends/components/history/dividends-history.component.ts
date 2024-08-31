import { ChangeDetectionStrategy, Component } from '@angular/core';
import { RangeEnum } from '@app/models/enums/range-enum';

@Component({
    templateUrl: 'dividends-history.component.html',
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
}
