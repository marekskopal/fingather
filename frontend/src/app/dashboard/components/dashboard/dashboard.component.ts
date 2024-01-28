import { Component, OnInit } from '@angular/core';
import { first } from 'rxjs/operators';

import {CurrencyService, GroupWithGroupDataService, PortfolioService} from '@app/services';
import {Currency, GroupWithGroupData} from "@app/models";

@Component({ templateUrl: 'dashboard.component.html' })
export class DashboardComponent implements OnInit {
    public groupsWithGroupData: GroupWithGroupData[]|null = null;
    public defaultCurrency: Currency;

    public constructor(
        private readonly groupWithGroupDataService: GroupWithGroupDataService,
        private readonly currencyService: CurrencyService,
        private readonly portfolioService: PortfolioService,
    ) {}

    public async ngOnInit(): Promise<void> {
        const portfolio = await this.portfolioService.getDefaultPortfolio();
        this.defaultCurrency = await this.currencyService.getDefaultCurrency();

        this.groupWithGroupDataService.getGroupWithGroupData(portfolio.id)
            .pipe(first())
            .subscribe((groupsWithGroupData: GroupWithGroupData[]) => this.groupsWithGroupData = groupsWithGroupData);
    }
}
