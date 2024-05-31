import {
    ChangeDetectionStrategy, Component, OnInit, signal
} from '@angular/core';
import { Currency, GroupWithGroupData } from '@app/models';
import { CurrencyService, GroupWithGroupDataService, PortfolioService } from '@app/services';

@Component({
    templateUrl: 'dashboard.component.html',
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class DashboardComponent implements OnInit {
    protected readonly $groupsWithGroupData = signal<GroupWithGroupData[] | null>(null);
    protected defaultCurrency: Currency;

    public constructor(
        private readonly groupWithGroupDataService: GroupWithGroupDataService,
        private readonly currencyService: CurrencyService,
        private readonly portfolioService: PortfolioService,
    ) {}

    public async ngOnInit(): Promise<void> {
        this.defaultCurrency = await this.currencyService.getDefaultCurrency();

        this.refreshGroupWithGroupData();

        this.portfolioService.subscribe(() => {
            this.refreshGroupWithGroupData();
        });
    }

    public async refreshGroupWithGroupData(): Promise<void> {
        this.$groupsWithGroupData.set(null);

        const portfolio = await this.portfolioService.getCurrentPortfolio();

        const groupsWithGroupData = await this.groupWithGroupDataService.getGroupWithGroupData(portfolio.id);
        this.$groupsWithGroupData.set(groupsWithGroupData);
    }
}
