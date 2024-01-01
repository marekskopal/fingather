import { Component, OnInit } from '@angular/core';
import { first } from 'rxjs/operators';

import {NgbModal} from "@ng-bootstrap/ng-bootstrap";
import {AddEditComponent} from "./add-edit.component";
import {Asset, Currency} from "@app/models";
import {AssetService, CurrencyService} from "@app/services";

@Component({ templateUrl: 'list.component.html' })
export class ListComponent implements OnInit {
    public assets: Asset[]|null = null;
    public defaultCurrency: Currency;

    public constructor(
        private assetService: AssetService,
        private modalService: NgbModal,
        private currencyService: CurrencyService,
    ) {}

    public async ngOnInit(): Promise<void> {
        this.defaultCurrency = await this.currencyService.getDefaultCurrency();

        this.assetService.getAssets()
            .pipe(first())
            .subscribe(assets => this.assets = assets);
    }

    public addAsset(): void {
        this.modalService.open(AddEditComponent);
    }

    public deleteAsset(id: number): void {
        this.assetService.deleteAsset(id)
            .pipe(first())
            .subscribe(() => this.assets = this.assets !== null ? this.assets.filter(x => x.id !== id) : null);
    }
}
