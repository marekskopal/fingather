import { ChangeDetectionStrategy, Component, OnInit } from '@angular/core';
import { Asset } from '@app/models';
import { RangeEnum } from '@app/models/enums/range-enum';
import { AssetService, PortfolioService } from '@app/services';

@Component({
    templateUrl: 'history.component.html',
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class HistoryComponent implements OnInit {
    public range: RangeEnum = RangeEnum.SevenDays;
    public assets: Asset[] = [];
    public benchmarkAssetId: number | null = null;

    public constructor(
        private readonly assetService: AssetService,
        private readonly portfolioService: PortfolioService,
    ) {
    }

    public async ngOnInit(): Promise<void> {
        this.refreshAssets();

        this.portfolioService.eventEmitter.subscribe(() => {
            this.refreshAssets();
        });
    }

    public async refreshAssets(): Promise<void> {
        this.assets = [];

        const portfolio = await this.portfolioService.getCurrentPortfolio();

        this.assets = await this.assetService.getAssets(portfolio.id);
    }

    public changeRange(range: RangeEnum): void {
        this.range = range;
    }

    public changeBenchmarkAsset(event: Event): void {
        const eventTarget = event.target as HTMLSelectElement;
        this.benchmarkAssetId = eventTarget.value.length > 0 ? parseInt(eventTarget.value, 10) : null;
    }

    protected readonly PortfolioDataRangeEnum = RangeEnum;
}
