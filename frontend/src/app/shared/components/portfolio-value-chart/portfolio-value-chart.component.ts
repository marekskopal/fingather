import {
    Component, input, InputSignal, OnChanges, OnInit
} from '@angular/core';
import { PortfolioDataWithBenchmarkData } from '@app/models';
import { RangeEnum } from '@app/models/enums/range-enum';
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
    public range: InputSignal<RangeEnum> = input.required<RangeEnum>();
    public benchmarkAssetId: InputSignal<number | null> = input<number | null>(null);
    public height: InputSignal<string> = input<string>('auto');
    public showLabels: InputSignal<boolean | null> = input<boolean | null>(null);
    public title: InputSignal<string | null> = input<string | null>(null);
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
                this.chartOptions.series[0].data = chartMap.valueSeries;
                this.chartOptions.series[1].data = chartMap.investedValueSeries;
                if (chartMap.benchmarkSeries.length > 0) {
                    this.chartOptions.series[2].data = chartMap.benchmarkSeries;
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
                {
                    name: 'Invested Value',
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
            colors: ['#64ee85', '#6bf5ff']
        };

        if (this.benchmarkAssetId() !== null) {
            this.chartOptions.series[1] = {
                name: 'Benchmark',
                data: [],
            };

            this.chartOptions.colors[1] = '#d063ee';
        }
    }

    private initializeBenchmarkChartOptions(): void {
        if (this.benchmarkAssetId() === null) {
            return;
        }

        this.chartOptions.series[2] = {
            name: 'Benchmark',
            data: [],
        };

        this.chartOptions.colors[2] = '#d063ee';
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
            benchmarkSeries: this.benchmarkAssetId() !== null
                ? portfolioDatas.map((portfolioData) => parseFloat(portfolioData.benchmarkData?.value ?? '0.0'))
                : [],
            categories: portfolioDatas.map((portfolioData) => portfolioData.date)
        };
    }
}
