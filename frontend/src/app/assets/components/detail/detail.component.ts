import {
    ChangeDetectionStrategy, Component, inject, OnInit, signal
} from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { AssetWithProperties, Currency } from '@app/models';
import { AssetService, CurrencyService } from '@app/services';

@Component({
    templateUrl: 'detail.component.html',
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class DetailComponent implements OnInit {
    private readonly assetService = inject(AssetService);
    private readonly currencyService =  inject(CurrencyService);
    private readonly route = inject(ActivatedRoute);

    private $asset = signal<AssetWithProperties | null>(null);
    protected defaultCurrency: Currency;
    protected tickerCurrency: Currency;
    private id: number;

    public async ngOnInit(): Promise<void> {
        this.id = this.route.snapshot.params['id'];

        this.$asset.set(await this.assetService.getAsset(this.id));
        this.defaultCurrency = await this.currencyService.getDefaultCurrency();
        const currenciesMap = await this.currencyService.getCurrenciesMap();
        if (this.asset) {
            this.tickerCurrency = currenciesMap.get(this.asset.ticker.currencyId) as Currency;
        }
    }

    protected get asset(): AssetWithProperties | null {
        return this.$asset();
    }
}
