import {
    ChangeDetectionStrategy, Component, input, InputSignal
} from '@angular/core';

@Component({
    selector: 'fingather-value-color',
    templateUrl: 'value-color.component.html',
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ValueColorComponent {
    public value: InputSignal<number> = input.required<number>();
}
