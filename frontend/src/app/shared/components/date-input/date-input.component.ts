import {
    ChangeDetectionStrategy, Component, computed, inject, input, output, signal,
} from '@angular/core';
import { AlertService } from '@app/services';
import { ConfirmDialogService } from '@app/services/confirm-dialog.service';
import {ControlValueAccessor, NG_VALUE_ACCESSOR} from "@angular/forms";

@Component({
    selector: 'fingather-date-input',
    templateUrl: 'date-input.component.html',
    changeDetection: ChangeDetectionStrategy.OnPush,
    providers: [
        {
            provide: NG_VALUE_ACCESSOR,
            multi:true,
            useExisting: DateInputComponent
        }
    ]
})
export class DateInputComponent implements ControlValueAccessor {
    public readonly $id = input.required<string>({
        alias: 'id',
    });
    public readonly $placeholder = input<string>('', {
        alias: 'placeholder',
    });
    public readonly $datetime = input<boolean>(false, {
        alias: 'datetime',
    });

    protected value: string | null = null;
    private touched: boolean = false;
    protected disabled: boolean = false;

    protected readonly $inputType = computed<string>(() => {
        return this.$datetime() ? 'datetime-local' : 'date';
    });

    private onChange = (value: string | null) => {};
    private onTouched = () => {};

    public writeValue(value: string | null) {
        this.value = value;
    }

    public registerOnChange(onChange: any) {
        this.onChange = onChange;
    }

    public registerOnTouched(onTouched: any) {
        this.onTouched = onTouched;
    }

    public setDisabledState(disabled: boolean) {
        this.disabled = disabled;
    }

    protected selectDate(event: Event): void {
        const element = event.target as HTMLElement;
        const input: HTMLInputElement = element as HTMLInputElement;

        this.value = input.value;
        if (this.value === '') {
            this.value = null;
        }

        this.onChange(this.value)
        this.onTouched();

        this.markAsTouched();
    }

    protected dateClick(event: Event): void {
        const element = event.target as HTMLElement;
        let input: HTMLInputElement = element as HTMLInputElement;
        if (element.tagName !== 'INPUT') {
            input = element.getElementsByTagName('input')[0];
        }

        this.showPicker(input);
    }

    protected onInputFocus(event: Event): void {
        const element = event.target as HTMLInputElement;
        element.type = this.$inputType();
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
        } catch (error) {
            // ignore
        }
    }
}
