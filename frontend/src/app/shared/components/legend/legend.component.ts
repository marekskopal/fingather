import {
    ChangeDetectionStrategy, Component, input,
} from '@angular/core';
import {LegendItem} from "@app/shared/components/legend/types/legend-item";

@Component({
    selector: 'fingather-legend',
    templateUrl: 'legend.component.html',
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class LegendComponent {
    public readonly $legendItems = input.required<LegendItem[]>({
        alias: 'legendItems',
    });
}
