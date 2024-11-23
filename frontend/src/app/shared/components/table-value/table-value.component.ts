import {
    ChangeDetectionStrategy, Component, input, InputSignal,
} from '@angular/core';
import {ValueColorComponent} from "@app/shared/components/value-color/value-color.component";

@Component({
    selector: 'fingather-table-value',
    templateUrl: 'table-value.component.html',
    imports: [
        ValueColorComponent,
    ],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class TableValueComponent {
    public value: InputSignal<number | null> = input.required<number | null>();
}
