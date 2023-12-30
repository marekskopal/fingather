import { Component, OnInit } from '@angular/core';
import { first } from 'rxjs/operators';

import {CurrencyService, PortfolioService} from '@app/services';
import {Currency, Portfolio} from "../models";

@Component({ templateUrl: 'list.component.html' })
export class ListComponent implements OnInit {
    public portfolio: Portfolio|null = null;
    public currencies: Map<number, Currency>;
    public defaultCurrency: Currency;

    constructor(
        private portfolioService: PortfolioService,
        private currencyService: CurrencyService,
    ) {}

    async ngOnInit() {
        this.currencies = await this.currencyService.getCurrencies();
        this.defaultCurrency = await this.currencyService.getDefaultCurrency();

        this.portfolioService.get()
            .pipe(first())
            .subscribe(portfolio => this.portfolio = portfolio);
    }
}
