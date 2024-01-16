import { Component } from '@angular/core';
import {PortfolioDataRangeEnum} from "@app/models";

@Component({ templateUrl: 'history.component.html' })
export class HistoryComponent {

    public range: PortfolioDataRangeEnum = PortfolioDataRangeEnum.SevenDays;

    public changeRange(range: PortfolioDataRangeEnum) {
        this.range = range;
        console.log(this.range);
    }

    protected readonly PortfolioDataRangeEnum = PortfolioDataRangeEnum;
}
