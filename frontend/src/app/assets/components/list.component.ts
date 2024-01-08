import {Component, OnDestroy, OnInit} from '@angular/core';
import { first } from 'rxjs/operators';

import {NgbModal} from "@ng-bootstrap/ng-bootstrap";
import {Asset, AssetWithProperties, Currency} from "@app/models";
import {AssetService, CurrencyService} from "@app/services";
import {AddAssetComponent} from "@app/assets/components/add-asset/add-asset.component";

@Component({ templateUrl: 'list.component.html' })
export class ListComponent implements OnInit, OnDestroy {
    public openedAssets: AssetWithProperties[]|null = null;
    public closedAssets: Asset[]|null = null;
    public watchedAssets: Asset[]|null = null;

    public defaultCurrency: Currency;

    public activeTab = 'open-positions';

    public constructor(
        private assetService: AssetService,
        private modalService: NgbModal,
        private currencyService: CurrencyService,
    ) {}

    public async ngOnInit(): Promise<void> {
        this.defaultCurrency = await this.currencyService.getDefaultCurrency();

        this.assetService.getOpenedAssets()
            .pipe(first())
            .subscribe((openedAssets: AssetWithProperties[]) => this.openedAssets = openedAssets);

        this.assetService.getClosedAssets()
            .pipe(first())
            .subscribe((closedAssets: Asset[]) => this.closedAssets = closedAssets);

        this.assetService.getWatchedAssets()
            .pipe(first())
            .subscribe((watchedAssets: Asset[]) => this.watchedAssets = watchedAssets);

        this.assetService.eventEmitter.subscribe(() => {
            this.ngOnInit();
        });
    }

    public ngOnDestroy(): void {
        this.assetService.eventEmitter.unsubscribe();
    }

    public addAsset(): void {
        this.modalService.open(AddAssetComponent);
    }
}
