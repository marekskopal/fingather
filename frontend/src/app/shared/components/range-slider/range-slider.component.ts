import {
    ChangeDetectionStrategy,
    Component,
    input,
    InputSignal,
    output,
    OutputEmitterRef,
} from '@angular/core';

@Component({
    selector: 'fingather-range-slider',
    templateUrl: 'range-slider.component.html',
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class RangeSliderComponent {
    public readonly min: InputSignal<number> = input.required<number>();
    public readonly max: InputSignal<number> = input.required<number>();
    public readonly step: InputSignal<number> = input<number>(1);
    public readonly value: InputSignal<number> = input.required<number>();

    public readonly valueChange: OutputEmitterRef<number> = output<number>();

    protected onInput(event: Event): void {
        const inputElement = event.target as HTMLInputElement;
        const value = parseFloat(inputElement.value);
        this.valueChange.emit(value);
    }
}
