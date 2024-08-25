import {
    ChangeDetectionStrategy,
    Component, input, InputSignal, OnInit, signal,
} from '@angular/core';
import { AssetData } from '@app/models';
import { RangeEnum } from '@app/models/enums/range-enum';
import { AssetDataService } from '@app/services/asset-data.service';
import {
    ApexAxisChartSeries,
    ApexChart,
    ApexDataLabels,
    ApexFill,
    ApexGrid,
    ApexStroke,
    ApexTheme,
    ApexTitleSubtitle,
    ApexXAxis, ApexYAxis
} from 'ng-apexcharts';

export type ChartOptions = {
    series: ApexAxisChartSeries;
    chart: ApexChart;
    dataLabels: ApexDataLabels,
    stroke: ApexStroke,
    xaxis: ApexXAxis;
    yaxis: ApexYAxis;
    title: ApexTitleSubtitle;
    grid: ApexGrid;
    theme: ApexTheme,
    fill: ApexFill,
    colors: string[],
};

@Component({
    templateUrl: 'asset-value-chart.component.html',
    selector: 'fingather-asset-value-chart',
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class AssetValueChartComponent implements OnInit {
    public assetId: InputSignal<number> = input.required<number>();
    public height: InputSignal<string> = input<string>('auto');

    public chartOptions: ChartOptions;
    protected $loading = signal<boolean>(true);

    public constructor(
        private readonly assetDataService: AssetDataService,
    ) {
    }

    public ngOnInit(): void {
        this.initializeChartOptions();

        this.refreshChart();
    }

    private async refreshChart(): Promise<void> {
        this.$loading.set(true);

        const assetDatas = await this.assetDataService.getAssetDataRange(this.assetId(), RangeEnum.All);

        const mappedAssetData = this.mapAssetData(assetDatas);

        this.chartOptions.xaxis.categories = mappedAssetData.categories;
        this.chartOptions.series[0].data = mappedAssetData.gainSeries;
        this.chartOptions.series[1].data = mappedAssetData.transactionValueSeries;

        this.$loading.set(false);
    }

    private initializeChartOptions(): void {
        this.chartOptions = {
            series: [
                {
                    name: 'Gain/Loss',
                    data: [],
                    zIndex: 2,
                },
                {
                    name: 'Invested value',
                    data: [],
                    zIndex: 1,
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
                },
                animations: {
                    enabled: false
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
            grid: {
                borderColor: '#454545',
            },
            xaxis: {
                type: 'datetime',
                categories: [],
                labels: {
                    style: {
                        colors: '#b0b0b0'
                    }
                },
                axisBorder: {
                    color: '#454545'
                },
                axisTicks: {
                    color: '#454545'
                }
            },
            yaxis: {
                labels: {
                    style: {
                        colors: '#b0b0b0'
                    },
                    formatter: (value: number): string | string[] => {
                        return value.toFixed(2);
                    }
                },
            },
            theme: {
                mode: 'dark',
            },
            fill: {
                type: 'gradient',
                gradient: {
                    shade: 'dark',
                    shadeIntensity: 0.9,
                    inverseColors: false,
                    opacityFrom: 0.8,
                    opacityTo: 0,
                    stops: [0, 90, 100]
                },
            },
            colors: ['#9e2af3', '#7597f2'],
        };
    }

    private mapAssetData(assetDatas: AssetData[]): {
        gainSeries: number[],
        transactionValueSeries: number[],
        categories: string[]
    } {
        return {
            gainSeries: assetDatas.map(
                (assetData) => parseFloat(assetData.transactionValueDefaultCurrency)
                    + parseFloat(assetData.gainDefaultCurrency)
            ),
            transactionValueSeries: assetDatas.map(
                (assetData) => parseFloat(assetData.transactionValueDefaultCurrency)
            ),
            categories: assetDatas.map((assetData) => assetData.date)
        };
    }
}
