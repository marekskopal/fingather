import {
    ChangeDetectionStrategy, Component, input, InputSignal
} from '@angular/core';

@Component({
    selector: 'fingather-value-color',
    templateUrl: 'value-color.component.html',
    standalone: true,
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ValueColorComponent {
    public value: InputSignal<number> = input.required<number>();
}
