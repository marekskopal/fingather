import {ChangeDetectionStrategy, Component, OnInit, signal} from '@angular/core';
import { Asset } from '@app/models';
import { RangeEnum } from '@app/models/enums/range-enum';
import { AssetService, PortfolioService } from '@app/services';

@Component({
    templateUrl: 'history.component.html',
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class HistoryComponent implements OnInit {
    protected range: RangeEnum = RangeEnum.SevenDays;
    private readonly $assets = signal<Asset[]>([]);
    public readonly $benchmarkAssetId = signal<number | null>(null);

    public constructor(
        private readonly assetService: AssetService,
        private readonly portfolioService: PortfolioService,
    ) {
    }

    public async ngOnInit(): Promise<void> {
        this.refreshAssets();

        this.portfolioService.subscribe(() => {
            this.refreshAssets();
        });
    }

    protected get assets(): Asset[] {
        return this.$assets();
    }

    private async refreshAssets(): Promise<void> {
        this.$assets.set([]);

        const portfolio = await this.portfolioService.getCurrentPortfolio();

        const assets = await this.assetService.getAssets(portfolio.id);
        this.$assets.set(assets);
    }

    protected changeRange(range: RangeEnum): void {
        this.range = range;
    }

    protected changeBenchmarkAsset(event: Event): void {
        const eventTarget = event.target as HTMLSelectElement;
        this.$benchmarkAssetId.set(eventTarget.value.length > 0 ? parseInt(eventTarget.value, 10) : null);
    }

    protected readonly PortfolioDataRangeEnum = RangeEnum;
}
