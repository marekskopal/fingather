import {Component, OnInit} from '@angular/core';
import {Asset, PortfolioDataRangeEnum} from "@app/models";
import {AssetService, PortfolioService} from "@app/services";

@Component({ templateUrl: 'history.component.html' })
export class HistoryComponent implements OnInit {
    public range: PortfolioDataRangeEnum = PortfolioDataRangeEnum.SevenDays;
    public assets: Asset[] = [];
    public benchmarkAssetId: number|null;

    public constructor(
        private readonly assetService: AssetService,
        private readonly portfolioService: PortfolioService,
    ) {
    }

    public async ngOnInit(): Promise<void> {
        const portfolio = await this.portfolioService.getCurrentPortfolio();

        this.assetService.getAssets(portfolio.id).subscribe((assets: Asset[]) => {
            this.assets = assets;
        });
    }

    public changeRange(range: PortfolioDataRangeEnum): void {
        this.range = range;
    }

    public changeBenchmarkAsset(event: Event): void {
        const eventTarget = event.target as HTMLSelectElement;
        this.benchmarkAssetId = eventTarget.value.length > 0 ? parseInt(eventTarget.value) : null;
    }

    protected readonly PortfolioDataRangeEnum = PortfolioDataRangeEnum;
}
