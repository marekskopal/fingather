import {AsyncPipe, DecimalPipe} from "@angular/common";
import {
    ChangeDetectionStrategy, Component, inject, input, OnInit, signal,
} from '@angular/core';
import {GroupChartComponent} from "@app/dashboard/components/group-data/components/group-chart/group-chart.component";
import { Currency } from '@app/models';
import {AbstractGroupWithGroupDataEntity} from "@app/models/abstract-group-with-group-data-entity";
import { CurrencyService, PortfolioService } from '@app/services';
import {GroupAllocationService} from "@app/services/group-allocation-service";
import {ColoredValueDirective} from "@app/shared/directives/colored-value.directive";
import {MoneyPipe} from "@app/shared/pipes/money.pipe";
import {ScrollShadowDirective} from "@marekskopal/ng-scroll-shadow";
import { TranslatePipe} from "@ngx-translate/core";

@Component({
    selector: 'fingather-group-allocation',
    templateUrl: 'group-allocation.component.html',
    imports: [
        TranslatePipe,
        GroupChartComponent,
        ColoredValueDirective,
        DecimalPipe,
        MoneyPipe,
        AsyncPipe,
        ScrollShadowDirective,
    ],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class GroupAllocationComponent implements OnInit {
    private readonly currencyService = inject(CurrencyService);
    private readonly portfolioService = inject(PortfolioService);

    public readonly $groupsAllocationService = input.required<GroupAllocationService>({
        alias: 'groupsAllocationService',
    });

    protected readonly $groupsWithGroupData = signal<AbstractGroupWithGroupDataEntity[] | null>(null);
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

        const groupsWithGroupData = await this.$groupsAllocationService().getGroupAllocations(portfolio.id);
        this.$groupsWithGroupData.set(groupsWithGroupData);
    }
}
