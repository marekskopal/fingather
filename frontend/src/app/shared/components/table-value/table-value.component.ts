import {
    ChangeDetectionStrategy, Component, input, InputSignal
} from '@angular/core';

@Component({
    selector: 'fingather-table-value',
    templateUrl: 'table-value.component.html',
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class TableValueComponent {
    public value: InputSignal<number | null> = input.required<number | null>();
}
