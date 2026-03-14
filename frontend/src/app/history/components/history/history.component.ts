import {
    ChangeDetectionStrategy, Component, computed, DestroyRef, inject, OnInit, signal,
} from '@angular/core';
import { MatIcon } from '@angular/material/icon';
import {
    CorrelationHeatmapComponent,
} from '@app/history/components/correlation-heatmap/correlation-heatmap.component';
import {
    RiskMetricsComponent,
} from '@app/history/components/risk-metrics/risk-metrics.component';
import { Asset, BenchmarkAsset, PortfolioRiskData } from '@app/models';
import { RangeEnum } from '@app/models/enums/range-enum';
import { SamplingFrequencyEnum } from '@app/models/enums/sampling-frequency-enum';
import {
    AssetService, BenchmarkAssetService, PortfolioRiskDataService, PortfolioService,
} from '@app/services';
import {AssetSelectorComponent} from "@app/shared/components/asset-selector/asset-selector.component";
import {
    BenchmarkAssetSelectorComponent,
} from "@app/shared/components/benchmark-asset-selector/benchmark-asset-selector.component";
import {DateInputComponent} from "@app/shared/components/date-input/date-input.component";
import { HelpComponent } from '@app/shared/components/help/help.component';
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
        RiskMetricsComponent,
        CorrelationHeatmapComponent,
        MatIcon,
        HelpComponent,
    ],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class HistoryComponent implements OnInit {
    private readonly assetService = inject(AssetService);
    private readonly benchmarkAssetService = inject(BenchmarkAssetService);
    private readonly portfolioService = inject(PortfolioService);
    private readonly portfolioRiskDataService = inject(PortfolioRiskDataService);
    private readonly destroyRef = inject(DestroyRef);

    protected activeRange: RangeEnum = RangeEnum.YTD;
    protected customRangeFrom: string | null = null;
    protected customRangeTo: string | null = null;
    protected readonly assets = signal<Asset[]>([]);
    protected readonly fixedBenchmarkAssets = signal<BenchmarkAsset[]>([]);
    protected readonly benchmarkAssetId = signal<number | null>(null);
    protected readonly benchmarkTickerId = signal<number | null>(null);
    protected readonly riskData = signal<PortfolioRiskData | null>(null);
    protected readonly riskLoading = signal<boolean>(false);
    protected readonly riskError = signal<boolean>(false);
    protected readonly samplingFrequency = signal<SamplingFrequencyEnum>(SamplingFrequencyEnum.Weekly);
    protected samplingFrequencyManuallySet = false;

    protected readonly samplingOptions: { value: SamplingFrequencyEnum, label: string }[] = [
        { value: SamplingFrequencyEnum.Daily, label: 'app.history.risk.samplingDaily' },
        { value: SamplingFrequencyEnum.Weekly, label: 'app.history.risk.samplingWeekly' },
        { value: SamplingFrequencyEnum.Monthly, label: 'app.history.risk.samplingMonthly' },
    ];

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
        this.applyDefaultSamplingForRange();

        this.refreshAssets();
        this.loadFixedBenchmarkAssets();
        this.refreshRiskData();

        this.portfolioService.subscribe(() => {
            this.refreshAssets();
            this.refreshRiskData();
        }, this.destroyRef);
    }

    private applyDefaultSamplingForRange(): void {
        if (this.samplingFrequencyManuallySet) {
            return;
        }

        // Defaults: < 3M → Daily, ≥ 3M & not All → Weekly, All → Monthly.
        const shortRanges = new Set<RangeEnum>([
            RangeEnum.SevenDays, RangeEnum.OneMonth, RangeEnum.Custom,
        ]);
        let frequency: SamplingFrequencyEnum;
        if (shortRanges.has(this.activeRange)) {
            frequency = SamplingFrequencyEnum.Daily;
        } else if (this.activeRange === RangeEnum.All) {
            frequency = SamplingFrequencyEnum.Monthly;
        } else {
            frequency = SamplingFrequencyEnum.Weekly;
        }
        this.samplingFrequency.set(frequency);
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

    private async refreshRiskData(): Promise<void> {
        this.riskLoading.set(true);
        this.riskError.set(false);

        try {
            const portfolio = await this.portfolioService.getCurrentPortfolio();
            const data = await this.portfolioRiskDataService.getPortfolioRiskData(
                portfolio.id,
                this.activeRange,
                this.samplingFrequency(),
                this.benchmarkTickerId(),
                this.customRangeFrom,
                this.customRangeTo,
            );
            this.riskData.set(data);
        } catch {
            this.riskError.set(true);
        } finally {
            this.riskLoading.set(false);
        }
    }

    protected changeActiveRange(activeRange: RangeEnum): void {
        this.activeRange = activeRange;
        this.applyDefaultSamplingForRange();
        this.refreshRiskData();
    }

    protected changeCustomRangeFrom(event: Event): void {
        this.activeRange = RangeEnum.Custom;

        const target = event.target as HTMLInputElement;
        this.customRangeFrom = target.value;
        this.applyDefaultSamplingForRange();
        this.refreshRiskData();
    }

    protected changeCustomRangeTo(event: Event): void {
        this.activeRange = RangeEnum.Custom;

        const target = event.target as HTMLInputElement;
        this.customRangeTo = target.value;
        this.applyDefaultSamplingForRange();
        this.refreshRiskData();
    }

    protected changeSamplingFrequency(frequency: SamplingFrequencyEnum): void {
        this.samplingFrequencyManuallySet = true;
        this.samplingFrequency.set(frequency);
        this.refreshRiskData();
    }

    protected changeBenchmarkAsset(asset: Asset): void {
        this.benchmarkTickerId.set(null);
        this.benchmarkAssetId.set(asset.id);
        this.refreshRiskData();
    }

    protected selectFixedBenchmark(benchmarkAsset: BenchmarkAsset): void {
        this.benchmarkAssetId.set(null);
        this.benchmarkTickerId.set(benchmarkAsset.ticker.id);
        this.refreshRiskData();
    }

    protected readonly RangeEnum = RangeEnum;
    protected readonly SamplingFrequencyEnum = SamplingFrequencyEnum;
}
