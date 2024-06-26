import {
    ChangeDetectionStrategy,
    Component, input, InputSignal, OnInit, signal,
} from '@angular/core';
import { TickerData } from '@app/models';
import { AssetService, TickerDataService } from '@app/services';
import {
    ApexAnnotations,
    ApexAxisChartSeries,
    ApexChart, ApexDataLabels, ApexFill, ApexStroke,
    ApexTheme,
    ApexTitleSubtitle,
    ApexXAxis
} from 'ng-apexcharts';

export type ChartOptions = {
    series: ApexAxisChartSeries;
    chart: ApexChart;
    dataLabels: ApexDataLabels,
    stroke: ApexStroke,
    xaxis: ApexXAxis;
    annotations: ApexAnnotations;
    title: ApexTitleSubtitle;
    theme: ApexTheme,
    fill: ApexFill,
    colors: string[],
};

@Component({
    templateUrl: 'asset-ticker-chart.component.html',
    selector: 'fingather-asset-ticker-chart',
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class AssetTickerChartComponent implements OnInit {
    public assetId: InputSignal<number> = input.required<number>();
    public assetTickerId: InputSignal<number> = input.required<number>();
    public chartOptions: ChartOptions;
    protected $loading = signal<boolean>(true);

    public constructor(
        private readonly tickerDataService: TickerDataService,
        private readonly assetService: AssetService,
    ) {
    }

    public ngOnInit(): void {
        this.initializeChartOptions();

        this.refreshChart();
    }

    private async refreshChart(): Promise<void> {
        this.$loading.set(true);

        const assetTickerDatas = await this.tickerDataService.getTickerDatas(this.assetTickerId());

        const assetTickerData = this.mapAssetTickerData(assetTickerDatas);

        this.chartOptions.xaxis.categories = assetTickerData.categories;
        this.chartOptions.series[0].data = assetTickerData.series;

        const asset = await this.assetService.getAsset(this.assetId());

        // @ts-expect-error yaxis is always an array
        this.chartOptions.annotations.yaxis[0].y = asset.averagePrice;
        // @ts-expect-error yaxis is always an array
        this.chartOptions.annotations.yaxis[0].label.text = `Average Buy Price - ${asset.averagePrice}`;

        this.$loading.set(false);
    }

    private initializeChartOptions(): void {
        this.chartOptions = {
            series: [
                {
                    name: 'Price',
                    data: [],
                },
            ],
            chart: {
                height: 500,
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
                text: '',
                align: 'left'
            },
            xaxis: {
                type: 'datetime',
                categories: [],
            },
            annotations: {
                yaxis: [
                    {
                        y: 0,
                        borderColor: '#6bf5ff',
                        label: {
                            borderColor: '#6bf5ff',
                            style: {
                                color: '#1b2627',
                                background: '#6bf5ff',
                            },
                            text: 'Average Buy Price - ',
                        }
                    }
                ]
            },
            theme: {
                mode: 'dark'
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
    }

    private mapAssetTickerData(assetTickerDatas: TickerData[]): { series: number[], categories: string[] } {
        return {
            series: assetTickerDatas.map((data) => parseFloat(data.close)),
            categories: assetTickerDatas.map((data) => data.date)
        };
    }
}
