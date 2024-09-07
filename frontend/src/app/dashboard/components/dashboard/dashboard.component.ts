import {AsyncPipe, DecimalPipe} from "@angular/common";
import {
    ChangeDetectionStrategy, Component, inject, OnInit, signal
} from '@angular/core';
import {MatIcon} from "@angular/material/icon";
import {GroupChartComponent} from "@app/dashboard/components/group-chart/group-chart.component";
import { Currency, GroupWithGroupData } from '@app/models';
import { CurrencyService, GroupWithGroupDataService, PortfolioService } from '@app/services';
import {PortfolioSelectorComponent} from "@app/shared/components/portfolio-selector/portfolio-selector.component";
import {PortfolioTotalComponent} from "@app/shared/components/portfolio-total/portfolio-total.component";
import {ColoredValueDirective} from "@app/shared/directives/colored-value.directive";
import {MoneyPipe} from "@app/shared/pipes/money.pipe";
import {TranslateModule} from "@ngx-translate/core";

@Component({
    templateUrl: 'dashboard.component.html',
    standalone: true,
    imports: [
        PortfolioSelectorComponent,
        TranslateModule,
        PortfolioTotalComponent,
        MatIcon,
        GroupChartComponent,
        ColoredValueDirective,
        DecimalPipe,
        MoneyPipe,
        AsyncPipe
    ],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class DashboardComponent implements OnInit {
    private readonly groupWithGroupDataService = inject(GroupWithGroupDataService);
    private readonly currencyService = inject(CurrencyService);
    private readonly portfolioService = inject(PortfolioService);

    protected readonly $groupsWithGroupData = signal<GroupWithGroupData[] | null>(null);
    protected defaultCurrency: Currency;

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
