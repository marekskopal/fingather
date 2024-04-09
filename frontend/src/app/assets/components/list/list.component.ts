import { Component, OnDestroy, OnInit } from '@angular/core';
import { AddAssetComponent } from '@app/assets/components/add-asset/add-asset.component';
import {
    Asset, AssetsWithProperties, Currency, GroupWithGroupData
} from '@app/models';
import { AssetsOrder } from '@app/models/enums/assets-order';
import {
    AssetService, CurrencyService, GroupWithGroupDataService, PortfolioService
} from '@app/services';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { first } from 'rxjs/operators';

@Component({ templateUrl: 'list.component.html' })
export class ListComponent implements OnInit, OnDestroy {
    public assetsWithProperties: AssetsWithProperties | null = null;
    public openedGroupedAssets: GroupWithGroupData[] | null = null;
    public closedAssets: Asset[] | null = null;
    public watchedAssets: Asset[] | null = null;

    public defaultCurrency: Currency;

    public activeTab = 'open-positions';

    public withGroups: boolean = false;
    public showPerAnnum: boolean = false;

    protected readonly AssetsOrder = AssetsOrder;
    public openedAssetsOrderBy: AssetsOrder = AssetsOrder.TickerName;

    public constructor(
        private readonly assetService: AssetService,
        private readonly modalService: NgbModal,
        private readonly currencyService: CurrencyService,
        private readonly groupWithGroupDataService: GroupWithGroupDataService,
        private readonly portfolioService: PortfolioService,
    ) {}

    public async ngOnInit(): Promise<void> {
        this.defaultCurrency = await this.currencyService.getDefaultCurrency();

        this.refreshOpenedAssets();

        this.portfolioService.eventEmitter.subscribe(() => {
            this.refreshOpenedAssets();
        });
    }

    private async refreshOpenedAssets(): Promise<void> {
        this.assetsWithProperties = null;
        this.openedGroupedAssets = null;

        const portfolio = await this.portfolioService.getCurrentPortfolio();

        if (this.withGroups) {
            this.groupWithGroupDataService.getGroupWithGroupData(portfolio.id, this.openedAssetsOrderBy)
                .pipe(first())
                .subscribe(
                    (openedGroupedAssets: GroupWithGroupData[]) => this.openedGroupedAssets = openedGroupedAssets
                );
        }

        this.assetService.getAssetsWithProperties(portfolio.id, this.openedAssetsOrderBy)
            .pipe(first())
            .subscribe(
                (assetsWithProperties: AssetsWithProperties) => this.assetsWithProperties = assetsWithProperties
            );
    }

    public ngOnDestroy(): void {
        this.assetService.eventEmitter.unsubscribe();
    }

    public addAsset(): void {
        this.modalService.open(AddAssetComponent);
    }

    public changeWithGroups(): void {
        this.withGroups = !this.withGroups;
        this.assetsWithProperties = null;
        this.openedGroupedAssets = null;

        this.refreshOpenedAssets();
    }

    public changeShowPerAnnum(): void {
        this.showPerAnnum = !this.showPerAnnum;
    }

    public changeOpenedAssetsOrderBy(orderBy: AssetsOrder): void {
        this.openedAssetsOrderBy = orderBy;

        this.refreshOpenedAssets();
    }
}
