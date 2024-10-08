import {DecimalPipe} from "@angular/common";
import {
    ChangeDetectionStrategy, Component, input,
} from '@angular/core';
import {LegendItem} from "@app/shared/components/legend/types/legend-item";
import {TranslateModule} from "@ngx-translate/core";

@Component({
    selector: 'fingather-legend',
    templateUrl: 'legend.component.html',
    standalone: true,
    imports: [
        TranslateModule,
        DecimalPipe
    ],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class LegendComponent {
    public readonly $legendItems = input.required<LegendItem[]>({
        alias: 'legendItems',
    });
}
