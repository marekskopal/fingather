import { Component, OnInit } from '@angular/core';
import { first } from 'rxjs/operators';

import {NgbModal} from "@ng-bootstrap/ng-bootstrap";
import {AddEditComponent} from "./add-edit.component";
import {Asset, Currency} from "@app/models";
import {AssetService, CurrencyService} from "@app/services";

@Component({ templateUrl: 'list.component.html' })
export class ListComponent implements OnInit {
    public assets: Asset[]|null = null;
    public currencies: Map<number, Currency>;
    public defaultCurrency: Currency;

    constructor(
        private assetService: AssetService,
        private modalService: NgbModal,
        private currencyService: CurrencyService,
    ) {}

    public async ngOnInit() {
        this.currencies = await this.currencyService.getCurrencies();
        this.defaultCurrency = await this.currencyService.getDefaultCurrency();

        this.assetService.findAll()
            .pipe(first())
            .subscribe(assets => this.assets = assets);
    }

    addAsset() {
        this.modalService.open(AddEditComponent);
    }

    deleteAsset(id: number) {
        this.assetService.delete(id)
            .pipe(first())
            .subscribe(() => this.assets = this.assets !== null ? this.assets.filter(x => x.id !== id) : null);
    }
}
