import { Component, OnInit } from '@angular/core';


import {first} from "rxjs/operators";
import {Currency, PortfolioData} from "@app/models";
import {CurrencyService, PortfolioDataService} from "@app/services";

@Component({ selector: 'fingather-portfolio-total', templateUrl: 'portfolio-total.component.html' })
export class PortfolioTotalComponent implements OnInit {
    public portfolioData: PortfolioData;
    public currencies: Map<number, Currency>;
    public defaultCurrency: Currency;

    constructor(
        private portfolioDataService: PortfolioDataService,
        private currencyService: CurrencyService,
    ) { }

    public async ngOnInit() {
        //TODO: fix currency race condition
        this.currencies = await this.currencyService.getCurrencies();
        this.defaultCurrency = await this.currencyService.getDefaultCurrency();

        this.portfolioDataService.getPortfolioData()
            .pipe(first())
            .subscribe((portfolioData) => {
                this.portfolioData = portfolioData;
            });
    }
}
