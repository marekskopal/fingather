import { Component, OnInit } from '@angular/core';


import {first} from "rxjs/operators";
import {Currency, PortfolioData} from "@app/models";
import {CurrencyService, PortfolioDataService, PortfolioService} from "@app/services";

@Component({ selector: 'fingather-portfolio-total', templateUrl: 'portfolio-total.component.html' })
export class PortfolioTotalComponent implements OnInit {
    public portfolioData: PortfolioData;
    public currencies: Map<number, Currency>;
    public defaultCurrency: Currency;

    public constructor(
        private readonly portfolioDataService: PortfolioDataService,
        private readonly currencyService: CurrencyService,
        private readonly portfolioService: PortfolioService,
    ) { }

    public async ngOnInit(): Promise<void> {
        const portfolio = await this.portfolioService.getCurrentPortfolio();
        this.currencies = await this.currencyService.getCurrenciesMap();
        this.defaultCurrency = await this.currencyService.getDefaultCurrency();

        this.portfolioDataService.getPortfolioData(portfolio.id)
            .pipe(first())
            .subscribe((portfolioData) => {
                this.portfolioData = portfolioData;
            });
    }
}
