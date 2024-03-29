import { Component, OnInit } from '@angular/core';
import { Currency, GroupWithGroupData } from '@app/models';
import { CurrencyService, GroupWithGroupDataService, PortfolioService } from '@app/services';
import { first } from 'rxjs/operators';

@Component({ templateUrl: 'dashboard.component.html' })
export class DashboardComponent implements OnInit {
    public groupsWithGroupData: GroupWithGroupData[] | null = null;
    public defaultCurrency: Currency;

    public constructor(
        private readonly groupWithGroupDataService: GroupWithGroupDataService,
        private readonly currencyService: CurrencyService,
        private readonly portfolioService: PortfolioService,
    ) {}

    public async ngOnInit(): Promise<void> {
        this.defaultCurrency = await this.currencyService.getDefaultCurrency();

        this.refreshGroupWithGroupData();

        this.portfolioService.eventEmitter.subscribe(() => {
            this.refreshGroupWithGroupData();
        });
    }

    public async refreshGroupWithGroupData(): Promise<void> {
        this.groupsWithGroupData = null;

        const portfolio = await this.portfolioService.getCurrentPortfolio();

        this.groupWithGroupDataService.getGroupWithGroupData(portfolio.id)
            .pipe(first())
            .subscribe((groupsWithGroupData: GroupWithGroupData[]) => this.groupsWithGroupData = groupsWithGroupData);
    }
}
