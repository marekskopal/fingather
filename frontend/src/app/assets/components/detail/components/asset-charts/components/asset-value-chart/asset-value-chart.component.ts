import {
    ChangeDetectionStrategy,
    Component, CSP_NONCE, inject, input, InputSignal, OnInit, signal,
} from '@angular/core';
import { AssetData } from '@app/models';
import { RangeEnum } from '@app/models/enums/range-enum';
import { CurrencyService } from '@app/services';
import { AssetDataService } from '@app/services/asset-data.service';
import {ChartUtils} from "@app/utils/chart-utils";
import { TranslateService } from '@ngx-translate/core';
import {
    ApexAxisChartSeries,
    ApexChart,
    ApexDataLabels,
    ApexFill,
    ApexGrid,
    ApexStroke,
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
    title: ApexTitleSubtitle;
    grid: ApexGrid;
    theme: ApexTheme,
    fill: ApexFill,
    tooltip: ApexTooltip,
    colors: string[],
};

@Component({
    templateUrl: 'asset-value-chart.component.html',
    selector: 'fingather-asset-value-chart',
    imports: [
        NgApexchartsModule,
    ],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class AssetValueChartComponent implements OnInit {
    private readonly assetDataService = inject(AssetDataService);
    private readonly currencyService = inject(CurrencyService);
    private readonly nonce = inject(CSP_NONCE);
    private readonly translateService = inject(TranslateService);

    public assetId: InputSignal<number> = input.required<number>();
    public height: InputSignal<string> = input<string>('auto');

    public chartOptions: ChartOptions;
    protected loading = signal<boolean>(true);

    public ngOnInit(): void {
        this.initializeChartOptions();

        this.refreshChart();
    }

    private async refreshChart(): Promise<void> {
        this.loading.set(true);

        const [assetDatas, defaultCurrency] = await Promise.all([
            this.assetDataService.getAssetDataRange(this.assetId(), RangeEnum.All),
            this.currencyService.getDefaultCurrency(),
        ]);

        const formatter = ChartUtils.currencyFormatter(defaultCurrency.symbol);
        this.chartOptions.yaxis = ChartUtils.yAxis(true, formatter);
        this.chartOptions.tooltip = { y: { formatter } };

        const mappedAssetData = this.mapAssetData(assetDatas);

        this.chartOptions.xaxis.categories = mappedAssetData.categories;
        this.chartOptions.series[0].data = mappedAssetData.gainSeries;
        this.chartOptions.series[1].data = mappedAssetData.transactionValueSeries;

        this.loading.set(false);
    }

    private initializeChartOptions(): void {
        this.chartOptions = {
            series: [
                {
                    name: this.translateService.instant('app.shared.charts.seriesGainLoss'),
                    data: [],
                    zIndex: 2,
                },
                {
                    name: this.translateService.instant('app.shared.charts.seriesInvestedValue'),
                    data: [],
                    zIndex: 1,
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
            grid: ChartUtils.grid(),
            xaxis: ChartUtils.xAxis(),
            yaxis: ChartUtils.yAxis(),
            theme: ChartUtils.theme(),
            fill: ChartUtils.gradientFill(),
            tooltip: {},
            colors: ChartUtils.colors(2),
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
                    + parseFloat(assetData.gainDefaultCurrency),
            ),
            transactionValueSeries: assetDatas.map(
                (assetData) => parseFloat(assetData.transactionValueDefaultCurrency),
            ),
            categories: assetDatas.map((assetData) => assetData.date),
        };
    }
}
