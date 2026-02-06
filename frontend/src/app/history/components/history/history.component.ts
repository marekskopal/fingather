import {
    ChangeDetectionStrategy, Component, computed, inject, OnInit, signal,
} from '@angular/core';
import { Asset, BenchmarkAsset } from '@app/models';
import { RangeEnum } from '@app/models/enums/range-enum';
import { AssetService, BenchmarkAssetService, PortfolioService } from '@app/services';
import {AssetSelectorComponent} from "@app/shared/components/asset-selector/asset-selector.component";
import {
    BenchmarkAssetSelectorComponent,
} from "@app/shared/components/benchmark-asset-selector/benchmark-asset-selector.component";
import {DateInputComponent} from "@app/shared/components/date-input/date-input.component";
import {LegendComponent} from "@app/shared/components/legend/legend.component";
import {LegendItem} from "@app/shared/components/legend/types/legend-item";
import {PortfolioSelectorComponent} from "@app/shared/components/portfolio-selector/portfolio-selector.component";
import {
    PortfolioValueChartComponent,
} from "@app/shared/components/portfolio-value-chart/portfolio-value-chart.component";
import {ColorEnum} from "@app/utils/enum/color-enum";
import {ScrollShadowDirective} from "@marekskopal/ng-scroll-shadow";
import { TranslatePipe} from "@ngx-translate/core";

@Component({
    templateUrl: 'history.component.html',
    imports: [
        TranslatePipe,
        PortfolioSelectorComponent,
        AssetSelectorComponent,
        BenchmarkAssetSelectorComponent,
        LegendComponent,
        PortfolioValueChartComponent,
        ScrollShadowDirective,
        DateInputComponent,
    ],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class HistoryComponent implements OnInit {
    private readonly assetService = inject(AssetService);
    private readonly benchmarkAssetService = inject(BenchmarkAssetService);
    private readonly portfolioService = inject(PortfolioService);

    protected activeRange: RangeEnum = RangeEnum.YTD;
    protected customRangeFrom: string | null = null;
    protected customRangeTo: string | null = null;
    protected readonly assets = signal<Asset[]>([]);
    protected readonly fixedBenchmarkAssets = signal<BenchmarkAsset[]>([]);
    protected readonly benchmarkAssetId = signal<number | null>(null);
    protected readonly benchmarkTickerId = signal<number | null>(null);

    protected ranges: {range: RangeEnum, text: string, number: number | null}[] = [
        {range: RangeEnum.SevenDays, text: 'app.history.history.d', number: 7},
        {range: RangeEnum.OneMonth, text: 'app.history.history.m', number: 1},
        {range: RangeEnum.ThreeMonths, text: 'app.history.history.m', number: 3},
        {range: RangeEnum.SixMonths, text: 'app.history.history.m', number: 6},
        {range: RangeEnum.YTD, text: 'app.history.history.ytd', number: null},
        {range: RangeEnum.OneYear, text: 'app.history.history.y', number: 1},
        {range: RangeEnum.All, text: 'app.history.history.all', number: null},
        {range: RangeEnum.Custom, text: 'app.history.history.custom', number: null},
    ];
    protected legendItems = computed<LegendItem[]>(() => {
        const legendItems: LegendItem[] = [
            {translation: 'app.history.history.value', color: ColorEnum.colorChart2},
            {translation: 'app.history.history.investedValue', color: ColorEnum.colorChart5},
        ];

        const benchmarkAssetId = this.benchmarkAssetId();
        const benchmarkTickerId = this.benchmarkTickerId();

        if (benchmarkAssetId !== null) {
            const benchmarkAsset = this.assets().find(asset => asset.id === benchmarkAssetId);
            legendItems.push({
                translation: 'app.history.history.benchmark',
                subName: benchmarkAsset?.ticker.ticker,
                color: ColorEnum.colorChart1,
            });
        } else if (benchmarkTickerId !== null) {
            const fixedBenchmark = this.fixedBenchmarkAssets().find(ba => ba.ticker.id === benchmarkTickerId);
            legendItems.push({
                translation: 'app.history.history.benchmark',
                subName: fixedBenchmark?.ticker.ticker,
                color: ColorEnum.colorChart1,
            });
        }

        return legendItems;
    })

    public async ngOnInit(): Promise<void> {
        this.refreshAssets();
        this.loadFixedBenchmarkAssets();

        this.portfolioService.subscribe(() => {
            this.refreshAssets();
        });
    }

    private async loadFixedBenchmarkAssets(): Promise<void> {
        const benchmarkAssets = await this.benchmarkAssetService.getBenchmarkAssets();
        this.fixedBenchmarkAssets.set(benchmarkAssets);
    }

    private async refreshAssets(): Promise<void> {
        this.assets.set([]);

        const portfolio = await this.portfolioService.getCurrentPortfolio();

        const assets = await this.assetService.getAssets(portfolio.id);
        this.assets.set(assets);
    }

    protected changeActiveRange(activeRange: RangeEnum): void {
        this.activeRange = activeRange;
    }

    protected changeCustomRangeFrom(event: Event): void {
        this.activeRange = RangeEnum.Custom;

        const target = event.target as HTMLInputElement;
        this.customRangeFrom = target.value;
    }

    protected changeCustomRangeTo(event: Event): void {
        this.activeRange = RangeEnum.Custom;

        const target = event.target as HTMLInputElement;
        this.customRangeTo = target.value;
    }

    protected changeBenchmarkAsset(asset: Asset): void {
        this.benchmarkTickerId.set(null);
        this.benchmarkAssetId.set(asset.id);
    }

    protected selectFixedBenchmark(benchmarkAsset: BenchmarkAsset): void {
        this.benchmarkAssetId.set(null);
        this.benchmarkTickerId.set(benchmarkAsset.ticker.id);
    }

    protected readonly RangeEnum = RangeEnum;
}
