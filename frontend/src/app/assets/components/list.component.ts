import {Component, OnDestroy, OnInit} from '@angular/core';
import { first } from 'rxjs/operators';

import {NgbModal} from "@ng-bootstrap/ng-bootstrap";
import {Asset, AssetWithProperties, Currency, GroupWithGroupData} from "@app/models";
import {AssetService, CurrencyService, GroupWithGroupDataService} from "@app/services";
import {AddAssetComponent} from "@app/assets/components/add-asset/add-asset.component";

@Component({ templateUrl: 'list.component.html' })
export class ListComponent implements OnInit, OnDestroy {
    public openedAssets: AssetWithProperties[]|null = null;
    public openedGroupedAssets: GroupWithGroupData[]|null = null;
    public closedAssets: Asset[]|null = null;
    public watchedAssets: Asset[]|null = null;

    public defaultCurrency: Currency;

    public activeTab = 'open-positions';

    public withGroups: boolean = false;

    public constructor(
        private readonly assetService: AssetService,
        private readonly modalService: NgbModal,
        private readonly currencyService: CurrencyService,
        private readonly groupWithGroupDataService: GroupWithGroupDataService,
    ) {}

    public async ngOnInit(): Promise<void> {
        this.defaultCurrency = await this.currencyService.getDefaultCurrency();

        this.refreshOpenedAssets();
        this.refreshClosedAssets();
        this.refreshWatchedAssets();
    }

    private refreshOpenedAssets(): void {
        if (this.withGroups) {
            this.groupWithGroupDataService.getGroupWithGroupData()
                .pipe(first())
                .subscribe((openedGroupedAssets: GroupWithGroupData[]) => this.openedGroupedAssets = openedGroupedAssets);

            this.openedAssets = null;

            return;
        }

        this.assetService.getOpenedAssets()
            .pipe(first())
            .subscribe((openedAssets: AssetWithProperties[]) => this.openedAssets = openedAssets);

        this.openedGroupedAssets = null;
    }

    private refreshClosedAssets(): void {
        this.assetService.getClosedAssets()
            .pipe(first())
            .subscribe((closedAssets: Asset[]) => this.closedAssets = closedAssets);
    }

    private refreshWatchedAssets(): void {
        this.assetService.getWatchedAssets()
            .pipe(first())
            .subscribe((watchedAssets: Asset[]) => this.watchedAssets = watchedAssets);
    }

    public ngOnDestroy(): void {
        this.assetService.eventEmitter.unsubscribe();
    }

    public addAsset(): void {
        this.modalService.open(AddAssetComponent);
    }

    public changeWithGroups(): void {
        this.withGroups = !this.withGroups;
        this.openedAssets = null;
        this.openedGroupedAssets = null;

        this.refreshOpenedAssets();
    }
}
