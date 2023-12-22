﻿import { Component, Input, OnInit, ViewChild } from '@angular/core';
import { first } from 'rxjs/operators';
import { TickerDataService } from '../../../_services/ticker-data.service';
import { tickerData } from '../../../_models';
import { ApexAxisChartSeries, ApexChart, ApexTitleSubtitle, ApexXAxis, ApexYAxis, ChartComponent } from 'ng-apexcharts';

export type ChartOptions = {
    series: ApexAxisChartSeries;
    chart: ApexChart;
    xaxis: ApexXAxis;
    yaxis: ApexYAxis;
    title: ApexTitleSubtitle;
};

@Component({
    templateUrl: 'asset-ticker-chart.component.html',
    selector: 'chart',
})
export class AssetTickerChartComponent implements OnInit {
    @ViewChild("chart", { static: false }) chart: ChartComponent;
    @Input() public assetTickerId: number;
    public assetTickerDatas: tickerData[]|null = null;
    public chartOptions: Partial<ChartOptions>;

    constructor(
        private assetTickerDataService: TickerDataService,
    ) {
        this.initializeChartOptions();
    }

    ngOnInit() {
        this.assetTickerDataService.findLastYear(this.assetTickerId)
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
                type: "candlestick",
                height: 500
            },
            title: {
                text: "CandleStick Chart",
                align: "left"
            },
            xaxis: {
                type: "datetime"
            },
            yaxis: {
                tooltip: {
                    enabled: true
                }
            }
        };
    }

    private mapAssetTickerData(assetTickerDatas: tickerData[])
    {
        let chartData = [];

        for (let assetTickerData of assetTickerDatas) {

            chartData.push({
                x: new Date(assetTickerData.date),
                y: [
                    assetTickerData.open,
                    assetTickerData.high,
                    assetTickerData.low,
                    assetTickerData.close,
                ]
            });
        }

        return chartData
    }
}
