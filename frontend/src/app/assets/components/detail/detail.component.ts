import {AsyncPipe} from "@angular/common";
import {
    ChangeDetectionStrategy, Component, inject, OnInit, signal
} from '@angular/core';
import {MatIcon} from "@angular/material/icon";
import {ActivatedRoute, RouterLink} from '@angular/router';
import {AssetChartsComponent} from "@app/assets/components/detail/components/asset-charts/asset-charts.component";
import {AssetValueComponent} from "@app/assets/components/detail/components/asset-value/asset-value.component";
import {DividendListComponent} from "@app/assets/components/detail/components/dividends/dividend-list.component";
import {FundamentalsComponent} from "@app/assets/components/detail/components/funtamentals/fundamentals.component";
import {
    TransactionListComponent
} from "@app/assets/components/detail/components/transactions/transaction-list.component";
import { AssetWithProperties, Currency } from '@app/models';
import { AssetService, CurrencyService } from '@app/services';
import {PortfolioSelectorComponent} from "@app/shared/components/portfolio-selector/portfolio-selector.component";
import {TickerLogoComponent} from "@app/shared/components/ticker-logo/ticker-logo.component";
import {CurrencyCodePipe} from "@app/shared/pipes/currency-code.pipe";
import {TranslateModule} from "@ngx-translate/core";

@Component({
    templateUrl: 'detail.component.html',
    standalone: true,
    imports: [
        PortfolioSelectorComponent,
        RouterLink,
        MatIcon,
        TranslateModule,
        TickerLogoComponent,
        AssetChartsComponent,
        AssetValueComponent,
        FundamentalsComponent,
        TransactionListComponent,
        DividendListComponent,
        CurrencyCodePipe,
        AsyncPipe
    ],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class DetailComponent implements OnInit {
    private readonly assetService = inject(AssetService);
    private readonly currencyService =  inject(CurrencyService);
    private readonly route = inject(ActivatedRoute);

    private $asset = signal<AssetWithProperties | null>(null);
    protected defaultCurrency: Currency;
    private id: number;

    public async ngOnInit(): Promise<void> {
        this.id = this.route.snapshot.params['id'];

        this.$asset.set(await this.assetService.getAsset(this.id));
        this.defaultCurrency = await this.currencyService.getDefaultCurrency();
    }

    protected get asset(): AssetWithProperties | null {
        return this.$asset();
    }
}
