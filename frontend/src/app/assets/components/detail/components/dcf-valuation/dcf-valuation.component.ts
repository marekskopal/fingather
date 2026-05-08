import {DecimalPipe} from "@angular/common";
import {
    ChangeDetectionStrategy,
    Component, DestroyRef, inject, input, InputSignal, OnInit, signal,
} from '@angular/core';
import {takeUntilDestroyed} from '@angular/core/rxjs-interop';
import {MatIcon} from "@angular/material/icon";
import {TickerDcfValuation} from '@app/models/ticker-dcf-valuation';
import {TickerDcfValuationOverrides, TickerDcfValuationService} from '@app/services/ticker-dcf-valuation.service';
import {RangeSliderComponent} from "@app/shared/components/range-slider/range-slider.component";
import {TranslatePipe} from "@ngx-translate/core";
import {debounceTime, Subject} from 'rxjs';

@Component({
    templateUrl: 'dcf-valuation.component.html',
    selector: 'fingather-dcf-valuation',
    imports: [
        TranslatePipe,
        DecimalPipe,
        MatIcon,
        RangeSliderComponent,
    ],
    host: { style: 'display: contents' },
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class DcfValuationComponent implements OnInit {
    private readonly tickerDcfValuationService = inject(TickerDcfValuationService);
    private readonly destroyRef = inject(DestroyRef);

    public tickerId: InputSignal<number> = input.required<number>();

    protected readonly dcfValuation = signal<TickerDcfValuation | null>(null);
    protected readonly loading = signal<boolean>(false);

    // Slider models, expressed in display units (% for rates, integer for years).
    protected readonly waccPercent = signal<number>(8.5);
    protected readonly terminalGrowthRatePercent = signal<number>(2.5);
    protected readonly growthRatePercent = signal<number>(0);
    protected readonly projectionYears = signal<number>(5);

    private readonly refetch$ = new Subject<void>();
    private initialized = false;

    public async ngOnInit(): Promise<void> {
        this.refetch$
            .pipe(debounceTime(250), takeUntilDestroyed(this.destroyRef))
            .subscribe(() => {
                void this.fetch();
            });

        await this.fetch();
    }

    protected onWaccChange(value: number): void {
        this.waccPercent.set(value);
        this.refetch$.next();
    }

    protected onTerminalGrowthChange(value: number): void {
        this.terminalGrowthRatePercent.set(value);
        this.refetch$.next();
    }

    protected onGrowthRateChange(value: number): void {
        this.growthRatePercent.set(value);
        this.refetch$.next();
    }

    protected onProjectionYearsChange(value: number): void {
        this.projectionYears.set(value);
        this.refetch$.next();
    }

    private async fetch(): Promise<void> {
        this.loading.set(true);

        const overrides: TickerDcfValuationOverrides = this.initialized ? {
            wacc: this.waccPercent() / 100,
            terminalGrowthRate: this.terminalGrowthRatePercent() / 100,
            projectionYears: this.projectionYears(),
            growthRate: this.growthRatePercent() / 100,
        } : {};

        try {
            const valuation = await this.tickerDcfValuationService.getTickerDcfValuation(
                this.tickerId(),
                overrides,
            );
            this.dcfValuation.set(valuation);

            if (!this.initialized) {
                // Snapshot computed defaults into slider models on first load.
                this.waccPercent.set(this.toPercent(valuation.assumptions.wacc));
                this.terminalGrowthRatePercent.set(this.toPercent(valuation.assumptions.terminalGrowthRate));
                this.growthRatePercent.set(this.toPercent(valuation.assumptions.appliedGrowthRate));
                this.projectionYears.set(valuation.assumptions.projectionYears);
                this.initialized = true;
            }
        } catch {
            // DCF valuation not available for this ticker
        } finally {
            this.loading.set(false);
        }
    }

    private toPercent(fraction: number): number {
        return Math.round(fraction * 1000) / 10;
    }
}
