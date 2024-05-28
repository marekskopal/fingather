import {
    ChangeDetectionStrategy,
    Component, input, InputSignal, OnInit,
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
    ApexXAxis
} from 'ng-apexcharts';

export type ChartOptions = {
    series: ApexAxisChartSeries;
    chart: ApexChart;
    dataLabels: ApexDataLabels,
    stroke: ApexStroke,
    xaxis: ApexXAxis;
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
    public loading: boolean = true;

    public constructor(
        private readonly assetDataService: AssetDataService,
    ) {
    }

    public ngOnInit(): void {
        this.initializeChartOptions();

        this.refreshChart();
    }

    private async refreshChart(): Promise<void> {
        this.loading = true;

        const assetDatas = await this.assetDataService.getAssetDataRange(this.assetId(), RangeEnum.All);

        const mappedAssetData = this.mapAssetData(assetDatas);

        this.chartOptions.xaxis.categories = mappedAssetData.categories;
        this.chartOptions.series[0].data = mappedAssetData.gainSeries;
        this.chartOptions.series[1].data = mappedAssetData.transactionValueSeries;

        this.loading = false;
    }

    private initializeChartOptions(): void {
        this.chartOptions = {
            series: [
                {
                    name: 'Gain/Loss',
                    data: [],
                },
                {
                    name: 'Invested value',
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
                text: '',
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
                    show: true,
                },
                categories: [],
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
