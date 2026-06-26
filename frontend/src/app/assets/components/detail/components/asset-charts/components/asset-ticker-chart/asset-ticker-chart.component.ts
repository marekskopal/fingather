import {
    ChangeDetectionStrategy,
    Component, CSP_NONCE, inject, input, InputSignal, OnInit, signal,
} from '@angular/core';
import { TickerData } from '@app/models';
import { AssetService, CurrencyService, TickerDataService } from '@app/services';
import {ChartUtils} from "@app/utils/chart-utils";
import { TranslateService } from '@ngx-translate/core';
import {
    ApexAnnotations,
    ApexAxisChartSeries,
    ApexChart, ApexDataLabels, ApexFill, ApexGrid, ApexStroke,
    ApexTheme,
    ApexTitleSubtitle,
    ApexTooltip,
    ApexXAxis, ApexYAxis, NgApexchartsModule,
} from 'ng-apexcharts';

export type ChartOptions = {
    series: ApexAxisChartSeries;
    chart: ApexChart;
    dataLabels: ApexDataLabels,
    stroke: ApexStroke,
    xaxis: ApexXAxis;
    yaxis: ApexYAxis;
    annotations: ApexAnnotations;
    title: ApexTitleSubtitle;
    theme: ApexTheme,
    fill: ApexFill,
    grid: ApexGrid,
    tooltip: ApexTooltip,
    colors: string[],
};

@Component({
    templateUrl: 'asset-ticker-chart.component.html',
    selector: 'fingather-asset-ticker-chart',
    imports: [
        NgApexchartsModule,
    ],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class AssetTickerChartComponent implements OnInit {
    private readonly tickerDataService = inject(TickerDataService);
    private readonly assetService = inject(AssetService);
    private readonly currencyService = inject(CurrencyService);
    private readonly nonce = inject(CSP_NONCE);
    private readonly translateService = inject(TranslateService);

    public assetId: InputSignal<number> = input.required<number>();
    public assetTickerId: InputSignal<number> = input.required<number>();
    public height: InputSignal<string> = input<string>('auto');
    public chartOptions: ChartOptions;
    protected loading = signal<boolean>(true);

    public ngOnInit(): void {
        this.initializeChartOptions();

        this.refreshChart();
    }

    private async refreshChart(): Promise<void> {
        this.loading.set(true);

        const [assetTickerDatas, asset, currencies] = await Promise.all([
            this.tickerDataService.getTickerDatas(this.assetTickerId()),
            this.assetService.getAsset(this.assetId()),
            this.currencyService.getCurrenciesMap(),
        ]);

        const currencySymbol = currencies.get(asset.ticker.currencyId)?.symbol ?? '';
        const formatter = ChartUtils.currencyFormatter(currencySymbol);
        this.chartOptions.yaxis = ChartUtils.yAxis(true, formatter);
        this.chartOptions.tooltip = { y: { formatter } };

        const assetTickerData = this.mapAssetTickerData(assetTickerDatas);

        this.chartOptions.xaxis.categories = assetTickerData.categories;
        this.chartOptions.series[0].data = assetTickerData.series;

        // @ts-expect-error yaxis is always an array
        this.chartOptions.annotations.yaxis[0].y = asset.averagePrice;
        // @ts-expect-error yaxis is always an array
        this.chartOptions.annotations.yaxis[0].label.text =
            `${this.translateService.instant('app.assets.detail.charts.seriesAverageBuyPrice')} - ${formatter(Number(asset.averagePrice))}`;

        this.loading.set(false);
    }

    private initializeChartOptions(): void {
        this.chartOptions = {
            series: [
                {
                    name: this.translateService.instant('app.assets.detail.charts.seriesPrice'),
                    data: [],
                },
            ],
            chart: {
                ...ChartUtils.locale(this.translateService.currentLang() ?? 'en'),
                height: this.height(),
                type: 'area',
                zoom: {
                    enabled: false,
                },
                toolbar: {
                    show: false,
                },
                animations: {
                    enabled: false,
                },
                nonce: this.nonce ?? undefined,
            },
            dataLabels: {
                enabled: false,
            },
            stroke: {
                curve: 'smooth',
            },
            title: {
                text: '',
                align: 'left',
            },
            xaxis: ChartUtils.xAxis(),
            yaxis: ChartUtils.yAxis(),
            annotations: {
                yaxis: [
                    {
                        y: 0,
                        borderColor: '#7597f2',
                        label: {
                            borderColor: '#7597f2',
                            style: {
                                color: '#1b2627',
                                background: '#7597f2',
                            },
                            text: '',
                        },
                    },
                ],
            },
            theme: ChartUtils.theme(),
            fill: ChartUtils.gradientFill(),
            grid: ChartUtils.grid(),
            tooltip: {},
            colors: ChartUtils.colors(1),
        };
    }

    private mapAssetTickerData(assetTickerDatas: TickerData[]): { series: number[], categories: string[] } {
        return {
            series: assetTickerDatas.map((data) => parseFloat(data.close)),
            categories: assetTickerDatas.map((data) => data.date),
        };
    }
}
