import {DecimalPipe} from "@angular/common";
import {
    ChangeDetectionStrategy,
    Component,
    computed,
    input,
    InputSignal,
    Signal,
} from '@angular/core';
import {TickerDcfValuationStatus} from '@app/models/ticker-dcf-valuation';

@Component({
    selector: 'fingather-dcf-valuation-chip',
    templateUrl: 'dcf-valuation-chip.component.html',
    imports: [DecimalPipe],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class DcfValuationChipComponent {
    public readonly status: InputSignal<TickerDcfValuationStatus | null> =
        input.required<TickerDcfValuationStatus | null>();
    public readonly diffPercent: InputSignal<number | null> =
        input.required<number | null>();

    /** Magnitude shown in the chip — overvalued: positive, undervalued: positive (sign carried by class). */
    protected readonly magnitudePercent: Signal<number | null> = computed(() => {
        const diff = this.diffPercent();
        return diff === null ? null : Math.abs(diff);
    });

    protected readonly chipClass: Signal<string | null> = computed(() => {
        switch (this.status()) {
            case 'overvalued': return 'dcf-chip-overvalued';
            case 'undervalued': return 'dcf-chip-undervalued';
            case 'fairlyValued': return 'dcf-chip-fair';
            default: return null;
        }
    });

    protected readonly sign: Signal<string> = computed(() => {
        switch (this.status()) {
            case 'overvalued': return '+';
            case 'undervalued': return '−';
            default: return '';
        }
    });
}
