import {
    ChangeDetectionStrategy, Component, computed, inject, OnInit, signal
} from '@angular/core';
import { Asset } from '@app/models';
import { RangeEnum } from '@app/models/enums/range-enum';
import { AssetService, PortfolioService } from '@app/services';
import {LegendItem} from "@app/shared/components/legend/types/legend-item";
import {ColorEnum} from "@app/utils/enum/color-enum";
import {TranslateService} from "@ngx-translate/core";

@Component({
    templateUrl: 'history.component.html',
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class HistoryComponent implements OnInit {
    private readonly assetService = inject(AssetService);
    private readonly portfolioService = inject(PortfolioService);
    private readonly translateService = inject(TranslateService);

    protected activeRange: RangeEnum = RangeEnum.YTD;
    private readonly $assets = signal<Asset[]>([]);
    protected readonly $benchmarkAssetId = signal<number | null>(null);

    protected ranges: {range: RangeEnum, text: string, number: number | null}[] = [
        {range: RangeEnum.SevenDays, text: 'app.history.history.d', number: 7},
        {range: RangeEnum.OneMonth, text: 'app.history.history.m', number: 1},
        {range: RangeEnum.ThreeMonths, text: 'app.history.history.m', number: 3},
        {range: RangeEnum.SixMonths, text: 'app.history.history.m', number: 6},
        {range: RangeEnum.YTD, text: 'app.history.history.ytd', number: null},
        {range: RangeEnum.OneYear, text: 'app.history.history.y', number: 1},
        {range: RangeEnum.All, text: 'app.history.history.all', number: null},
    ];
    protected $legendItems = computed<LegendItem[]>(() => {
        const legendItems: LegendItem[] = [
            {translation: 'app.history.history.value', color: ColorEnum.colorChart2},
            {translation: 'app.history.history.investedValue', color: ColorEnum.colorChart5},
        ];
        if (this.$benchmarkAssetId() !== null) {
            const benchmarkAsset = this.assets.find(asset => asset.id === this.$benchmarkAssetId());

            legendItems.push({
                translation: 'app.history.history.benchmark',
                subName: benchmarkAsset?.ticker.ticker,
                color: ColorEnum.colorChart1
            });
        }
        return legendItems;
    })

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

    protected changeActiveRange(activeRange: RangeEnum): void {
        this.activeRange = activeRange;
    }

    protected changeBenchmarkAsset(asset: Asset): void {
        this.$benchmarkAssetId.set(asset.id);
    }
}
