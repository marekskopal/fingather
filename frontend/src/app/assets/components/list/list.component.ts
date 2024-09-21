import {
    ChangeDetectionStrategy, ChangeDetectorRef, Component, inject, OnInit, signal
} from '@angular/core';
import {MatIcon} from "@angular/material/icon";
import {RouterLink} from "@angular/router";
import {ClosedAssetsComponent} from "@app/assets/components/list/components/closed-assets/closed-assets.component";
import {OpenedAssetsComponent} from "@app/assets/components/list/components/opened-assets/opened-assets.component";
import {
    OpenedGroupedAssetsComponent
} from "@app/assets/components/list/components/opened-grouped-assets/opened-grouped-assets.component";
import {WatchedAssetsComponent} from "@app/assets/components/list/components/watched-assets/watched-assets.component";
import {AssetsTabEnum} from "@app/assets/components/list/enums/assets-tab-enum";
import { AssetsWithProperties, Currency, GroupWithGroupData } from '@app/models';
import { AssetsOrder } from '@app/models/enums/assets-order';
import {
    AssetService, CurrencyService, GroupWithGroupDataService, PortfolioService
} from '@app/services';
import {PortfolioSelectorComponent} from "@app/shared/components/portfolio-selector/portfolio-selector.component";
import {ScrollShadowDirective} from "@marekskopal/ng-scroll-shadow";
import {NgbNav, NgbNavContent, NgbNavItem, NgbNavLinkButton, NgbNavOutlet} from "@ng-bootstrap/ng-bootstrap";
import {TranslateModule} from "@ngx-translate/core";

@Component({
    templateUrl: 'list.component.html',
    standalone: true,
    imports: [
        NgbNav,
        NgbNavItem,
        NgbNavLinkButton,
        NgbNavContent,
        RouterLink,
        TranslateModule,
        MatIcon,
        PortfolioSelectorComponent,
        OpenedGroupedAssetsComponent,
        OpenedAssetsComponent,
        ClosedAssetsComponent,
        WatchedAssetsComponent,
        NgbNavOutlet,
        ScrollShadowDirective
    ],
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

    protected activeTab: AssetsTabEnum = AssetsTabEnum.OpenedPositions;

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
            const openedGroupedAssets = await this.groupWithGroupDataService.getGroupsWithGroupData(
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

    protected readonly AssetsTabEnum = AssetsTabEnum;
}
