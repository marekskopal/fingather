import {Component, OnInit} from '@angular/core';
import {first} from "rxjs/operators";
import {Currency, YearCalculatedData} from "@app/models";
import {CurrencyService, OverviewService, PortfolioService} from "@app/services";


@Component({
    templateUrl: './list.component.html'
})
export class ListComponent implements OnInit {
    public yearCalculatedDatas: YearCalculatedData[]|null = null;
    public defaultCurrency: Currency;

    public constructor(
        private readonly overviewService: OverviewService,
        private readonly currencyService: CurrencyService,
        private readonly portfolioService: PortfolioService,
    ) {
    }

    public async ngOnInit(): Promise<void> {
        this.defaultCurrency = await this.currencyService.getDefaultCurrency();

        this.refreshYearCalculatedData();

        this.portfolioService.eventEmitter.subscribe(() => {
            this.refreshYearCalculatedData();
        });
    }

    public async refreshYearCalculatedData(): Promise<void> {
        this.yearCalculatedDatas = null;

        const portfolio = await this.portfolioService.getCurrentPortfolio();

        this.overviewService.getYearCalculatedData(portfolio.id)
            .pipe(first())
            .subscribe(yearCalculatedDatas => this.yearCalculatedDatas = yearCalculatedDatas);
    }
}
