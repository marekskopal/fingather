import {
    ChangeDetectionStrategy, Component, computed, input,
} from '@angular/core';
import {ControlValueAccessor, NG_VALUE_ACCESSOR} from "@angular/forms";

@Component({
    selector: 'fingather-date-input',
    templateUrl: 'date-input.component.html',
    standalone: true,
    changeDetection: ChangeDetectionStrategy.OnPush,
    providers: [
        {
            provide: NG_VALUE_ACCESSOR,
            multi:true,
            useExisting: DateInputComponent,
        },
    ],
})
export class DateInputComponent implements ControlValueAccessor {
    public readonly id = input.required<string>();
    public readonly placeholder = input<string>('');
    public readonly datetime = input<boolean>(false);

    protected value: string | null = null;
    private touched: boolean = false;
    protected disabled: boolean = false;

    protected readonly inputType = computed<string>(() => {
        return this.datetime() ? 'datetime-local' : 'date';
    });

    //eslint-disable-next-line unused-imports/no-unused-vars
    private onChange = (value: string | null): void => {};
    private onTouched = (): void => {};

    public writeValue(value: string | null): void {
        this.value = value;
    }

    public registerOnChange(onChange: any): void {
        this.onChange = onChange;
    }

    public registerOnTouched(onTouched: any): void {
        this.onTouched = onTouched;
    }

    public setDisabledState(disabled: boolean): void {
        this.disabled = disabled;
    }

    protected selectDate(event: Event): void {
        const inputElement = event.target as HTMLInputElement;

        this.value = inputElement.value;
        if (this.value === '') {
            this.value = null;
        }

        this.onChange(this.value)
        this.onTouched();

        this.markAsTouched();
    }

    protected dateClick(event: Event): void {
        const element = event.target as HTMLElement;
        let inputElement: HTMLInputElement = element as HTMLInputElement;
        if (element.tagName !== 'INPUT') {
            inputElement = element.getElementsByTagName('input')[0];
        }

        this.showPicker(inputElement);
    }

    protected onInputFocus(event: Event): void {
        const element = event.target as HTMLInputElement;
        element.type = this.inputType();
        this.showPicker(element);
    }

    protected onInputBlur(event: Event): void {
        if (this.value !== null) {
            return;
        }

        const element = event.target as HTMLInputElement;
        element.type = 'text';
    }

    private markAsTouched(): void {
        if (this.touched) {
            return;
        }
        this.touched = true;
    }

    private showPicker(element: HTMLInputElement): void {
        try {
            element.showPicker();
        } catch (error) { //eslint-disable-line unused-imports/no-unused-vars
            // ignore
        }
    }
}
