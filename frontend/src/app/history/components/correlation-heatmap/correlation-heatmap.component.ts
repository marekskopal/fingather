import {
    ChangeDetectionStrategy,
    Component, computed, CSP_NONCE, inject, input, signal,
} from '@angular/core';
import { PortfolioRiskData } from '@app/models';
import { SamplingFrequencyEnum } from '@app/models/enums/sampling-frequency-enum';
import { HelpComponent } from '@app/shared/components/help/help.component';
import { LegendComponent } from '@app/shared/components/legend/legend.component';
import { LegendItem } from '@app/shared/components/legend/types/legend-item';
import { ColorEnum } from '@app/utils/enum/color-enum';
import { TranslatePipe } from '@ngx-translate/core';
import {
    ApexAxisChartSeries,
    ApexChart,
    ApexDataLabels,
    ApexGrid,
    ApexPlotOptions,
    ApexStroke,
    ApexXAxis,
    ApexYAxis,
    NgApexchartsModule,
} from 'ng-apexcharts';

export type HeatmapChartOptions = {
    series: ApexAxisChartSeries;
    chart: ApexChart;
    xaxis: ApexXAxis;
    yaxis: ApexYAxis;
    dataLabels: ApexDataLabels;
    plotOptions: ApexPlotOptions;
    colors: string[];
    legend: { show: boolean };
    stroke: ApexStroke;
    grid: ApexGrid;
};

type CategoryKey = 'positive' | 'neutral' | 'negative';

const COLOR_POSITIVE = ColorEnum.colorChart2;
const COLOR_NEUTRAL = ColorEnum.colorGrayLighter;
const COLOR_NEGATIVE = ColorEnum.colorChart5;
const COLOR_HIDDEN = '#1f1f1f';
const COLOR_TEXT = ColorEnum.colorWhite;

const CATEGORY_RANGES: Record<CategoryKey, { from: number, to: number, color: string }> = {
    negative: { from: -1, to: -0.2, color: COLOR_NEGATIVE },
    neutral: { from: -0.2, to: 0.2, color: COLOR_NEUTRAL },
    positive: { from: 0.2, to: 1, color: COLOR_POSITIVE },
};

@Component({
    selector: 'fingather-correlation-heatmap',
    templateUrl: 'correlation-heatmap.component.html',
    changeDetection: ChangeDetectionStrategy.OnPush,
    imports: [
        NgApexchartsModule,
        TranslatePipe,
        LegendComponent,
        HelpComponent,
    ],
})
export class CorrelationHeatmapComponent {
    private readonly nonce = inject(CSP_NONCE);

    public readonly riskData = input.required<PortfolioRiskData>();
    public readonly samplingFrequency = input<SamplingFrequencyEnum>(SamplingFrequencyEnum.Daily);

    protected readonly hiddenCategories = signal<ReadonlySet<CategoryKey>>(new Set());

    protected readonly chartOptions = computed<HeatmapChartOptions>(() => this.buildChart());

    protected readonly legendItems = computed<LegendItem[]>(() => {
        const hidden = this.hiddenCategories();
        return [
            {
                key: 'positive',
                color: COLOR_POSITIVE,
                translation: 'app.history.risk.legendPositive',
                interactive: true,
                inactive: hidden.has('positive'),
            },
            {
                key: 'neutral',
                color: COLOR_NEUTRAL,
                translation: 'app.history.risk.legendNeutral',
                interactive: true,
                inactive: hidden.has('neutral'),
            },
            {
                key: 'negative',
                color: COLOR_NEGATIVE,
                translation: 'app.history.risk.legendNegative',
                interactive: true,
                inactive: hidden.has('negative'),
            },
        ];
    });

    protected readonly diversificationScore = computed<{
        value: number,
        levelKey: string,
        detailKey: string,
    } | null>(() => {
        const matrix = this.riskData().correlationMatrix;
        const n = matrix.length;
        if (n < 2) {
            return null;
        }

        let sum = 0;
        let count = 0;
        for (let i = 0; i < n; i++) {
            for (let j = i + 1; j < n; j++) {
                sum += matrix[i]?.[j] ?? 0;
                count++;
            }
        }
        if (count === 0) {
            return null;
        }

        const value = sum / count;
        // Daily returns are noisy: a concentrated single-sector portfolio averages ~0.25.
        // Weekly noise-smoothed: same portfolio jumps to ~0.35. Monthly: ~0.5.
        // Calibrated so the same portfolio gets the same label regardless of frequency.
        const thresholds = {
            [SamplingFrequencyEnum.Daily]: { high: 0.4, medium: 0.15 },
            [SamplingFrequencyEnum.Weekly]: { high: 0.55, medium: 0.25 },
            [SamplingFrequencyEnum.Monthly]: { high: 0.7, medium: 0.4 },
        }[this.samplingFrequency()];

        const tier = value >= thresholds.high
            ? 'High'
            : value >= thresholds.medium
                ? 'Medium'
                : 'Low';

        return {
            value,
            levelKey: `app.history.risk.diversification${tier}`,
            detailKey: `app.history.risk.diversification${tier}Detail`,
        };
    });

    protected toggleLegendItem(item: LegendItem): void {
        if (item.key !== 'positive' && item.key !== 'neutral' && item.key !== 'negative') {
            return;
        }
        const key = item.key;
        const next = new Set(this.hiddenCategories());
        if (next.has(key)) {
            next.delete(key);
        } else {
            next.add(key);
        }
        this.hiddenCategories.set(next);
    }

    private isValueHidden(val: number, hidden: ReadonlySet<CategoryKey>): boolean {
        for (const key of hidden) {
            const r = CATEGORY_RANGES[key];
            if (val >= r.from && val < r.to) {
                return true;
            }
            // Include upper bound for the top range so 1.0 still hides under 'positive'.
            if (key === 'positive' && val === r.to) {
                return true;
            }
        }
        return false;
    }

    private buildChart(): HeatmapChartOptions {
        const data = this.riskData();
        const labels = data.correlationLabels;
        const matrix = data.correlationMatrix;

        if (labels.length === 0) {
            return this.buildEmptyChart();
        }

        const hidden = this.hiddenCategories();

        const series: ApexAxisChartSeries = labels.map((rowLabel, i) => ({
            name: rowLabel,
            data: labels.map((colLabel, j) => ({
                x: colLabel,
                y: Math.round((matrix[i]?.[j] ?? 0) * 100) / 100,
            })),
        }));

        return {
            series,
            chart: {
                type: 'heatmap',
                height: Math.max(280, labels.length * 36),
                toolbar: { show: false },
                animations: { enabled: false },
                background: 'transparent',
                nonce: this.nonce ?? undefined,
            },
            dataLabels: {
                enabled: true,
                formatter: (val: number): string => (
                    this.isValueHidden(val, hidden) ? '' : val.toFixed(2)
                ),
                style: {
                    fontSize: '11px',
                    colors: [COLOR_TEXT],
                },
            },
            plotOptions: {
                heatmap: {
                    radius: 4,
                    enableShades: false,
                    useFillColorAsStroke: false,
                    colorScale: {
                        ranges: [
                            {
                                from: -1,
                                to: -0.2,
                                color: hidden.has('negative') ? COLOR_HIDDEN : COLOR_NEGATIVE,
                                name: '−1 to −0.2',
                            },
                            {
                                from: -0.2,
                                to: 0.2,
                                color: hidden.has('neutral') ? COLOR_HIDDEN : COLOR_NEUTRAL,
                                name: '−0.2 to 0.2',
                            },
                            {
                                from: 0.2,
                                to: 1,
                                color: hidden.has('positive') ? COLOR_HIDDEN : COLOR_POSITIVE,
                                name: '0.2 to 1',
                            },
                        ],
                    },
                },
            },
            xaxis: {
                type: 'category',
                labels: {
                    style: {
                        fontSize: '12px',
                        colors: COLOR_TEXT,
                    },
                    rotate: -45,
                },
                axisTicks: { show: false },
                axisBorder: { show: false },
            },
            yaxis: {
                labels: {
                    style: {
                        fontSize: '12px',
                        colors: COLOR_TEXT,
                    },
                },
                axisBorder: { show: false },
                axisTicks: { show: false },
            },
            colors: [COLOR_POSITIVE],
            legend: { show: false },
            stroke: {
                show: true,
                width: 1,
                colors: [COLOR_HIDDEN],
            },
            grid: { show: false },
        };
    }

    private buildEmptyChart(): HeatmapChartOptions {
        return {
            series: [],
            chart: {
                type: 'heatmap',
                height: 280,
                toolbar: { show: false },
                animations: { enabled: false },
                background: 'transparent',
                nonce: this.nonce ?? undefined,
            },
            dataLabels: { enabled: false },
            plotOptions: {},
            xaxis: {},
            yaxis: {},
            colors: [COLOR_POSITIVE],
            legend: { show: false },
            stroke: { show: false },
            grid: { show: false },
        };
    }
}
