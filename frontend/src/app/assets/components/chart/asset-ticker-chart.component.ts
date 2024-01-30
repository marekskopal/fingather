import { Component, Input, OnInit, ViewChild } from '@angular/core';
import {TickerData} from '@app/models';
import {TickerDataService} from '@app/services';
import {
    ApexAxisChartSeries,
    ApexChart,
    ApexTheme,
    ApexTitleSubtitle,
    ApexXAxis,
    ApexYAxis,
    ChartComponent
} from 'ng-apexcharts';
import { first } from 'rxjs/operators';

export type ChartOptions = {
    series: ApexAxisChartSeries;
    chart: ApexChart;
    xaxis: ApexXAxis;
    yaxis: ApexYAxis;
    title: ApexTitleSubtitle;
    theme: ApexTheme,
};

@Component({
    templateUrl: 'asset-ticker-chart.component.html',
    selector: 'fingather-asset-ticker-chart',
})
export class AssetTickerChartComponent implements OnInit {
    @ViewChild('chart', { static: false }) public chart: ChartComponent;
    @Input() public assetTickerId: string;
    public assetTickerDatas: TickerData[]|null = null;
    public chartOptions: ChartOptions;

    public constructor(
        private assetTickerDataService: TickerDataService,
    ) {
        this.initializeChartOptions();
    }

    public ngOnInit(): void {
        this.assetTickerDataService.getTickerDatas(parseInt(this.assetTickerId))
            .pipe(first())
            .subscribe(assetTickerDatas => {
                this.chartOptions.series = [{
                    data: this.mapAssetTickerData(assetTickerDatas)
                }];
            });
    }

    private initializeChartOptions(): void {
        this.chartOptions = {
            series: [
                {
                    data: []
                }
            ],
            chart: {
                type: 'candlestick',
                height: 500
            },
            title: {
                text: 'CandleStick Chart',
                align: 'left'
            },
            xaxis: {
                type: 'datetime'
            },
            yaxis: {
                tooltip: {
                    enabled: true
                }
            },
            theme: {
                mode: 'dark'
            }
        };
    }

    private mapAssetTickerData(assetTickerDatas: TickerData[]): {x: Date, y: number[]}[]
    {
        const chartData = [];

        for (const assetTickerData of assetTickerDatas) {

            chartData.push({
                x: new Date(assetTickerData.date),
                y: [
                    parseFloat(assetTickerData.open),
                    parseFloat(assetTickerData.high),
                    parseFloat(assetTickerData.low),
                    parseFloat(assetTickerData.close),
                ]
            });
        }

        return chartData
    }
}
