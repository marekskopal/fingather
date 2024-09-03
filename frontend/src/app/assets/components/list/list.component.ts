import {
    ChangeDetectionStrategy, ChangeDetectorRef, Component, inject, OnInit, signal
} from '@angular/core';
import { AssetsWithProperties, Currency, GroupWithGroupData } from '@app/models';
import { AssetsOrder } from '@app/models/enums/assets-order';
import {
    AssetService, CurrencyService, GroupWithGroupDataService, PortfolioService
} from '@app/services';

@Component({
    templateUrl: 'list.component.html',
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ListComponent implements OnInit {
    private readonly assetService = inject(AssetService);
    private readonly currencyService = inject(CurrencyService);
    private readonly groupWithGroupDataService = inject(GroupWithGroupDataService);
    private readonly portfolioService = inject(PortfolioService);
    private readonly changeDetectorRef = inject(ChangeDetectorRef);

    private readonly $assetsWithProperties = signal<AssetsWithProperties | null>(null);
    private readonly $openedGroupedAssets = signal<GroupWithGroupData[] | null>(null);

    protected defaultCurrency: Currency;

    protected activeTab = 'open-positions';

    private readonly $withGroups = signal<boolean>(false);
    protected readonly $showPerAnnum = signal<boolean>(false);

    protected readonly AssetsOrder = AssetsOrder;
    public openedAssetsOrderBy: AssetsOrder = AssetsOrder.TickerName;

    public async ngOnInit(): Promise<void> {
        this.defaultCurrency = await this.currencyService.getDefaultCurrency();

        this.refreshOpenedAssets();

        this.portfolioService.subscribe(() => {
            this.refreshOpenedAssets();
            this.changeDetectorRef.detectChanges();
        });
    }

    protected get assetsWithProperties(): AssetsWithProperties | null {
        return this.$assetsWithProperties();
    }

    protected get openedGroupedAssets(): GroupWithGroupData[] | null {
        return this.$openedGroupedAssets();
    }

    private async refreshOpenedAssets(): Promise<void> {
        this.$assetsWithProperties.set(null);
        this.$openedGroupedAssets.set(null);

        const portfolio = await this.portfolioService.getCurrentPortfolio();

        if (this.$withGroups()) {
            const openedGroupedAssets = await this.groupWithGroupDataService.getGroupWithGroupData(
                portfolio.id,
                this.openedAssetsOrderBy,
            );
            this.$openedGroupedAssets.set(openedGroupedAssets);
        }

        const assetsWithProperties = await this.assetService.getAssetsWithProperties(
            portfolio.id,
            this.openedAssetsOrderBy,
        );
        this.$assetsWithProperties.set(assetsWithProperties);
    }

    public changeWithGroups(): void {
        this.$withGroups.set(!this.$withGroups());
        this.$assetsWithProperties.set(null);
        this.$openedGroupedAssets.set(null);

        this.refreshOpenedAssets();
    }

    public changeShowPerAnnum(): void {
        this.$showPerAnnum.set(!this.$showPerAnnum());
    }

    public changeOpenedAssetsOrderBy(orderBy: AssetsOrder): void {
        this.openedAssetsOrderBy = orderBy;

        this.refreshOpenedAssets();
    }
}
