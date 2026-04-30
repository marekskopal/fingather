import {
    ChangeDetectionStrategy,
    Component, CSP_NONCE, effect, inject, input, signal,
} from '@angular/core';
import { FormsModule } from '@angular/forms';
import { DcaPlanProjection, Goal } from '@app/models';
import { GoalTypeEnum } from '@app/models/enums/goal-type-enum';
import { DcaPlanService } from '@app/services';
import { ChartUtils } from '@app/utils/chart-utils';
import { TranslatePipe, TranslateService } from '@ngx-translate/core';
import {
    ApexAnnotations, ApexAxisChartSeries, ApexChart, ApexDataLabels, ApexFill,
    ApexGrid, ApexLegend, ApexStroke, ApexTheme, ApexXAxis, ApexYAxis, NgApexchartsModule,
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
    annotations: ApexAnnotations;
};

@Component({
    selector: 'fingather-dca-plan-projection-chart',
    templateUrl: 'dca-plan-projection-chart.component.html',
    imports: [
        NgApexchartsModule,
        TranslatePipe,
        FormsModule,
    ],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class DcaPlanProjectionChartComponent {
    private readonly dcaPlanService = inject(DcaPlanService);
    private readonly nonce = inject(CSP_NONCE);
    private readonly translateService = inject(TranslateService);

    public readonly dcaPlanId = input.required<number>();
    public readonly goals = input<Goal[]>([]);

    protected readonly horizonYears = signal<number>(10);
    protected readonly withCurrentValue = signal<boolean>(true);
    protected readonly showSimulationBand = signal<boolean>(false);
    protected readonly loading = signal<boolean>(true);
    protected chartOptions: ChartOptions;

    protected readonly horizonOptions = [5, 10, 20, 30];

    private static readonly SimulationPaths = 10000;

    public constructor() {
        this.initializeChartOptions();

        effect(() => {
            const planId = this.dcaPlanId();
            if (planId > 0) {
                this.refreshChart(planId, this.horizonYears(), this.withCurrentValue(), this.showSimulationBand());
            }
        });

        effect(() => {
            this.chartOptions.annotations = this.buildGoalAnnotations(this.goals());
        });
    }

    protected setHorizon(years: number): void {
        this.horizonYears.set(years);
        this.refreshChart(this.dcaPlanId(), years, this.withCurrentValue(), this.showSimulationBand());
    }

    protected toggleWithCurrentValue(value: boolean): void {
        this.withCurrentValue.set(value);
        this.refreshChart(this.dcaPlanId(), this.horizonYears(), value, this.showSimulationBand());
    }

    protected toggleShowSimulationBand(value: boolean): void {
        this.showSimulationBand.set(value);
        this.refreshChart(this.dcaPlanId(), this.horizonYears(), this.withCurrentValue(), value);
    }

    private async refreshChart(
        planId: number,
        horizonYears: number,
        withCurrentValue: boolean,
        showSimulationBand: boolean,
    ): Promise<void> {
        this.loading.set(true);

        const simulations = showSimulationBand ? DcaPlanProjectionChartComponent.SimulationPaths : 0;
        const projection: DcaPlanProjection = await this.dcaPlanService.getProjection(
            planId,
            horizonYears,
            withCurrentValue,
            simulations,
        );

        const hasBands = showSimulationBand
            && projection.dataPoints.length > 0
            && projection.dataPoints[0].p10 != null
            && projection.dataPoints[0].p90 != null;

        // Reassign the whole options object so ng-apexcharts re-creates the chart cleanly when
        // switching between deterministic-only and mixed (rangeArea + line) modes.
        this.chartOptions = {
            ...this.chartOptions,
            chart: {
                ...this.chartOptions.chart,
                type: hasBands ? 'rangeArea' : 'area',
            },
            xaxis: {
                ...this.chartOptions.xaxis,
                categories: projection.dataPoints.map((p) => p.date),
            },
            series: this.buildSeries(projection, hasBands),
            colors: hasBands ? ChartUtils.colors(3) : ChartUtils.colors(2),
        };

        this.loading.set(false);
    }

    private buildSeries(projection: DcaPlanProjection, hasBands: boolean): ApexAxisChartSeries {
        if (!hasBands) {
            return [
                {
                    name: this.translateService.instant('app.dcaPlans.projection.seriesProjectedValue'),
                    data: projection.dataPoints.map((p) => parseFloat(p.projectedValue)),
                },
                {
                    name: this.translateService.instant('app.dcaPlans.projection.seriesInvestedCapital'),
                    data: projection.dataPoints.map((p) => parseFloat(p.investedCapital)),
                },
            ];
        }

        // For mixed rangeArea + line charts apex requires every series to use the {x, y} object form
        // (otherwise it tries to align plain number arrays against the rangeArea x-keys and crashes).
        return [
            {
                name: this.translateService.instant('app.dcaPlans.projection.seriesSimulationBand'),
                type: 'rangeArea',
                data: projection.dataPoints.map((p) => ({
                    x: p.date,
                    y: [parseFloat(p.p10 ?? p.investedCapital), parseFloat(p.p90 ?? p.projectedValue)],
                })),
            },
            {
                name: this.translateService.instant('app.dcaPlans.projection.seriesProjectedValue'),
                type: 'line',
                data: projection.dataPoints.map((p) => ({ x: p.date, y: parseFloat(p.projectedValue) })),
            },
            {
                name: this.translateService.instant('app.dcaPlans.projection.seriesInvestedCapital'),
                type: 'line',
                data: projection.dataPoints.map((p) => ({ x: p.date, y: parseFloat(p.investedCapital) })),
            },
        ];
    }

    private buildGoalAnnotations(goals: Goal[]): ApexAnnotations {
        const reachableGoals = goals.filter((g) => g.type !== GoalTypeEnum.ReturnPercentage);
        if (reachableGoals.length === 0) {
            return {};
        }

        return {
            yaxis: reachableGoals.map((goal) => ({
                y: parseFloat(goal.targetValue),
                borderColor: goal.isReachable === true ? '#28a745' : '#dc3545',
                strokeDashArray: 4,
                label: {
                    text: `${this.translateService.instant('app.goals.goals.reachability')}: ${parseFloat(goal.targetValue).toLocaleString()}`,
                    style: {
                        color: '#fff',
                        background: goal.isReachable === true ? '#28a745' : '#dc3545',
                    },
                },
            })),
        };
    }

    private initializeChartOptions(): void {
        this.chartOptions = {
            series: [
                {
                    name: this.translateService.instant('app.dcaPlans.projection.seriesProjectedValue'),
                    data: [],
                },
                {
                    name: this.translateService.instant('app.dcaPlans.projection.seriesInvestedCapital'),
                    data: [],
                },
            ],
            chart: {
                height: 'auto',
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
                curve: 'straight',
                width: 3,
            },
            grid: ChartUtils.grid(),
            xaxis: {
                ...ChartUtils.xAxis(true),
                type: 'category',
                categories: [],
            },
            yaxis: ChartUtils.yAxis(true),
            legend: {
                show: true,
            },
            theme: ChartUtils.theme(),
            fill: ChartUtils.gradientFill(),
            colors: ChartUtils.colors(2),
            annotations: {},
        };
    }
}
