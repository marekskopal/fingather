import {
    ChangeDetectionStrategy,
    Component, inject, input, OnChanges, OnInit, signal
} from '@angular/core';
import {Portfolio, PortfolioDataWithBenchmarkData} from '@app/models';
import { RangeEnum } from '@app/models/enums/range-enum';
import { PortfolioDataService, PortfolioService } from '@app/services';
import {ChartUtils} from "@app/utils/chart-utils";
import {
    ApexAxisChartSeries, ApexChart, ApexDataLabels, ApexFill,
    ApexGrid, ApexLegend, ApexStroke, ApexTheme, ApexXAxis, ApexYAxis, NgApexchartsModule
} from 'ng-apexcharts';

export type ChartOptions = {
    series: ApexAxisChartSeries;
    chart: ApexChart;
    xaxis: ApexXAxis;
    yaxis: ApexYAxis;
    dataLabels: ApexDataLabels;
    grid: ApexGrid;
    stroke: ApexStroke;
    legend: ApexLegend;
    theme: ApexTheme;
    fill: ApexFill;
    colors: string[];
};

@Component({
    templateUrl: 'portfolio-value-chart.component.html',
    selector: 'fingather-portfolio-value-chart',
    standalone: true,
    changeDetection: ChangeDetectionStrategy.OnPush,
    imports: [
        NgApexchartsModule
    ]
})
export class PortfolioValueChartComponent implements OnInit, OnChanges {
    private readonly portfolioDataService = inject(PortfolioDataService);
    private readonly portfolioService = inject(PortfolioService);

    public readonly $range = input.required<RangeEnum>({
        alias: 'range',
    });
    public readonly $portfolio = input<Portfolio | null>(null, {
        alias: 'portfolio',
    });
    public readonly $benchmarkAssetId = input<number | null>(null, {
        alias: 'benchmarkAssetId',
    });
    public readonly $height = input<string>('auto', {
        alias: 'height',
    });
    public readonly $showLabels = input<boolean>(true, {
        alias: 'showLabels',
    });
    public readonly $sparkline = input<boolean>(false, {
        alias: 'sparkline',
    });
    protected chartOptions: ChartOptions;
    protected readonly $loading = signal<boolean>(false);

    public ngOnInit(): void {
        this.initializeChartOptions();
        this.initializeBenchmarkChartOptions();

        this.refreshChart();

        this.portfolioService.subscribe(() => {
            this.refreshChart();
        });
    }

    public ngOnChanges(): void {
        this.initializeBenchmarkChartOptions();
        this.refreshChart();
    }

    private async refreshChart(): Promise<void> {
        this.$loading.set(true);

        const portfolio = this.$portfolio() ?? await this.portfolioService.getCurrentPortfolio();

        const portfolioData = await this.portfolioDataService.getPortfolioDataRange(
            portfolio.id,
            this.$range(),
            this.$benchmarkAssetId()
        );

        const chartMap = this.mapChart(portfolioData);
        this.chartOptions.xaxis.categories = chartMap.categories;
        this.chartOptions.series[0].data = chartMap.valueSeries;
        this.chartOptions.series[1].data = chartMap.investedValueSeries;
        if (chartMap.benchmarkSeries.length > 0) {
            this.chartOptions.series[2].data = chartMap.benchmarkSeries;
        }
        this.$loading.set(false);
    }

    private initializeChartOptions(): void {
        this.chartOptions = {
            series: [
                {
                    name: 'Value',
                    data: [],
                    zIndex: 3,
                },
                {
                    name: 'Invested Value',
                    data: [],
                    zIndex: 1,
                },
            ],
            chart: {
                height: this.$height(),
                type: 'area',
                zoom: {
                    enabled: false
                },
                toolbar: {
                    show: false
                },
                sparkline: {
                    enabled: this.$sparkline(),
                },
                animations: {
                    enabled: false,
                }
            },
            dataLabels: {
                enabled: false
            },
            stroke: {
                curve: 'straight',
                width: 3,
            },
            grid: ChartUtils.grid(),
            xaxis: ChartUtils.xAxis(this.$showLabels()),
            yaxis: ChartUtils.yAxis(this.$showLabels()),
            legend: {
                show: false,
            },
            theme: ChartUtils.theme(),
            fill: ChartUtils.gradientFill(),
            colors: ChartUtils.colors(3),
        };

        if (this.$benchmarkAssetId() !== null) {
            this.chartOptions.series[2] = {
                name: 'Benchmark',
                data: [],
            };
        }
    }

    private initializeBenchmarkChartOptions(): void {
        if (this.$benchmarkAssetId() === null) {
            return;
        }

        this.chartOptions.series[2] = {
            name: 'Benchmark',
            data: [],
            zIndex: 2,
        };
    }

    private mapChart(
        portfolioDatas: PortfolioDataWithBenchmarkData[]
    ): {
            valueSeries: number[],
            investedValueSeries: number[],
            benchmarkSeries: number[],
            categories: string[]
        } {
        return {
            valueSeries: portfolioDatas.map((portfolioData) => parseFloat(portfolioData.value)),
            investedValueSeries: portfolioDatas.map(
                (portfolioData) => parseFloat(portfolioData.transactionValue)
            ),
            benchmarkSeries: this.$benchmarkAssetId() !== null
                ? portfolioDatas.map((portfolioData) => parseFloat(portfolioData.benchmarkData?.value ?? '0.0'))
                : [],
            categories: portfolioDatas.map((portfolioData) => portfolioData.date)
        };
    }
}
