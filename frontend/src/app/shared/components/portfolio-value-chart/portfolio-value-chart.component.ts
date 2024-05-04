import {
    Component, input, InputSignal, OnChanges, OnInit
} from '@angular/core';
import { PortfolioDataRangeEnum, PortfolioDataWithBenchmarkData } from '@app/models';
import { PortfolioDataService, PortfolioService } from '@app/services';
import {
    ApexAxisChartSeries, ApexChart, ApexDataLabels, ApexFill, ApexGrid, ApexLegend, ApexStroke, ApexTheme,
    ApexTitleSubtitle, ApexXAxis, ApexYAxis
} from 'ng-apexcharts';
import { first } from 'rxjs/operators';

export type ChartOptions = {
    series: ApexAxisChartSeries;
    chart: ApexChart;
    xaxis: ApexXAxis;
    yaxis: ApexYAxis;
    dataLabels: ApexDataLabels;
    grid: ApexGrid;
    stroke: ApexStroke;
    title: ApexTitleSubtitle;
    legend: ApexLegend;
    theme: ApexTheme;
    fill: ApexFill;
    colors: string[];
};

@Component({
    templateUrl: 'portfolio-value-chart.component.html',
    selector: 'fingather-portfolio-value-chart',
})
export class PortfolioValueChartComponent implements OnInit, OnChanges {
    public range: InputSignal<PortfolioDataRangeEnum> = input.required<PortfolioDataRangeEnum>();
    public benchmarkAssetId: InputSignal<number|null> = input<number|null>(null);
    public height: InputSignal<string> = input<string>('auto');
    public showLabels: InputSignal<boolean|null> = input<boolean|null>(null);
    public title: InputSignal<string|null> = input<string|null>(null);
    public chartOptions: ChartOptions;
    public loading: boolean = true;

    public constructor(
        private readonly portfolioDataService: PortfolioDataService,
        private readonly portfolioService: PortfolioService,
    ) {
    }

    public ngOnInit(): void {
        this.initializeChartOptions();
        this.initializeBenchmarkChartOptions();

        this.refreshChart();

        this.portfolioService.eventEmitter.subscribe(() => {
            this.refreshChart();
        });
    }

    public ngOnChanges(): void {
        this.initializeBenchmarkChartOptions();
        this.refreshChart();
    }

    private async refreshChart(): Promise<void> {
        this.loading = true;

        const portfolio = await this.portfolioService.getCurrentPortfolio();

        this.portfolioDataService.getPortfolioDataRange(portfolio.id, this.range(), this.benchmarkAssetId())
            .pipe(first())
            .subscribe((portfolioData: PortfolioDataWithBenchmarkData[]) => {
                const chartMap = this.mapChart(portfolioData);
                this.chartOptions.xaxis.categories = chartMap.categories;
                this.chartOptions.series[0].data = chartMap.series;
                if (chartMap.benchmarkSeries.length > 0) {
                    this.chartOptions.series[1].data = chartMap.benchmarkSeries;
                }
                this.loading = false;
            });
    }

    private initializeChartOptions(): void {
        this.chartOptions = {
            series: [
                {
                    name: 'Value',
                    data: [],
                },
            ],
            chart: {
                height: this.height(),
                type: 'area',
                zoom: {
                    enabled: false
                },
                toolbar: {
                    show: false
                }
            },
            dataLabels: {
                enabled: false
            },
            stroke: {
                curve: 'smooth'
            },
            title: {
                text: this.title() ?? '',
                align: 'left'
            },
            grid: {
                row: {
                    colors: ['#2b3035', 'transparent'],
                    opacity: 0.5
                }
            },
            xaxis: {
                type: 'datetime',
                labels: {
                    show: this.showLabels() ?? true,
                },
                categories: [],
            },
            yaxis: {
                labels: {
                    show: this.showLabels() ?? true,
                }
            },
            legend: {
                show: this.showLabels() ?? true,
            },
            theme: {
                mode: 'dark',
            },
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    inverseColors: false,
                    opacityFrom: 0.5,
                    opacityTo: 0,
                    stops: [0, 90, 100]
                },
            },
            colors: ['#64ee85']
        };

        if (this.benchmarkAssetId() !== null) {
            this.chartOptions.series[1] = {
                name: 'Benchmark',
                data: [],
            };

            this.chartOptions.colors[1] = '#6bf5ff';
        }
    }

    private initializeBenchmarkChartOptions() {
        if (this.benchmarkAssetId() === null) {
            return;
        }

        this.chartOptions.series[1] = {
            name: 'Benchmark',
            data: [],
        };

        this.chartOptions.colors[1] = '#6bf5ff';
    }

    private mapChart(
        portfolioDatas: PortfolioDataWithBenchmarkData[]
    ): { series: number[], benchmarkSeries: number[], categories: string[] } {
        const series: number[] = [];
        const benchmarkSeries: number[] = [];
        const categories: string[] = [];

        for (const portfolioData of portfolioDatas) {
            series.push(portfolioData.value);
            categories.push(portfolioData.date);

            if (portfolioData.benchmarkData !== null) {
                benchmarkSeries.push(portfolioData.benchmarkData.value);
            }
        }

        return {
            series,
            benchmarkSeries,
            categories,
        };
    }
}
