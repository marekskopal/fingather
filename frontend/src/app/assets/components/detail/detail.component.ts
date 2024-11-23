import {AsyncPipe} from "@angular/common";
import {
    ChangeDetectionStrategy, Component, inject, OnInit, signal,
} from '@angular/core';
import {MatIcon} from "@angular/material/icon";
import {ActivatedRoute, RouterLink} from '@angular/router';
import {AssetChartsComponent} from "@app/assets/components/detail/components/asset-charts/asset-charts.component";
import {AssetValueComponent} from "@app/assets/components/detail/components/asset-value/asset-value.component";
import {FundamentalsComponent} from "@app/assets/components/detail/components/funtamentals/fundamentals.component";
import {AssetWithProperties, Currency, TransactionActionType} from '@app/models';
import { AssetService, CurrencyService } from '@app/services';
import {PortfolioSelectorComponent} from "@app/shared/components/portfolio-selector/portfolio-selector.component";
import {TickerLogoComponent} from "@app/shared/components/ticker-logo/ticker-logo.component";
import {CurrencyCodePipe} from "@app/shared/pipes/currency-code.pipe";
import {
    TransactionGridColumnEnum,
} from "@app/transactions/components/transaction-list/enums/transaction-grid-column-enum";
import {TransactionListComponent} from "@app/transactions/components/transaction-list/transaction-list.component";
import { TranslatePipe} from "@ngx-translate/core";

@Component({
    templateUrl: 'detail.component.html',
    imports: [
        PortfolioSelectorComponent,
        RouterLink,
        MatIcon,
        TranslatePipe,
        TickerLogoComponent,
        AssetChartsComponent,
        AssetValueComponent,
        FundamentalsComponent,
        CurrencyCodePipe,
        AsyncPipe,
        TransactionListComponent,
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

    protected readonly TransactionActionType = TransactionActionType;
    protected readonly TransactionGridColumnEnum = TransactionGridColumnEnum;
}
