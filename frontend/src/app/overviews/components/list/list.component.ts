import {Component, OnInit} from '@angular/core';
import {first} from "rxjs/operators";
import {Currency, YearCalculatedData} from "@app/models";
import {CurrencyService, OverviewService} from "@app/services";


@Component({
    templateUrl: './list.component.html'
})
export class ListComponent implements OnInit {
    public yearCalculatedDatas: YearCalculatedData[]|null = null;
    public defaultCurrency: Currency;

    public constructor(
        private overviewService: OverviewService,
        private currencyService: CurrencyService,
    ) {
    }

    public async ngOnInit() {
        this.defaultCurrency = await this.currencyService.getDefaultCurrency();

        this.overviewService.getYearCalculatedData()
            .pipe(first())
            .subscribe(yearCalculatedDatas => this.yearCalculatedDatas = yearCalculatedDatas);
    }
}
