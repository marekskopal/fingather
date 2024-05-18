import { Component, OnInit } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { AssetWithProperties, Currency } from '@app/models';
import { AssetService, CurrencyService } from '@app/services';

@Component({ templateUrl: 'detail.component.html' })
export class DetailComponent implements OnInit {
    protected asset: AssetWithProperties | null = null;
    protected defaultCurrency: Currency;
    private id: number;

    public constructor(
        private readonly assetService: AssetService,
        private readonly currencyService: CurrencyService,
        private readonly route: ActivatedRoute,
    ) {}

    public async ngOnInit(): Promise<void> {
        this.id = this.route.snapshot.params['id'];

        this.asset = await this.assetService.getAsset(this.id);
        this.defaultCurrency = await this.currencyService.getDefaultCurrency();
    }
}
