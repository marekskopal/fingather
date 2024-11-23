import {
    ChangeDetectionStrategy, Component, input,
} from '@angular/core';
import {ControlValueAccessor} from "@angular/forms";
import {SelectItem} from "@app/shared/types/select-item";

@Component({
    standalone: true,'template': '',
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export abstract class BaseSelectComponent<K extends keyof any, V> implements ControlValueAccessor {
    public readonly id = input.required<string>();
    public readonly items = input.required<SelectItem<K, V>[]>();
    public readonly placeholder = input<string>('');

    protected value: SelectItem<K, V> | null = null;
    private touched: boolean = false;
    protected disabled: boolean = false;

    //eslint-disable-next-line unused-imports/no-unused-vars
    private onChange = (value: K | null): void => {};
    private onTouched = (): void => {};

    public writeValue(value: K | null): void {
        this.value = this.getValueFromItems(value);
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

    protected onSelect(value: K | null): void {
        this.value = this.getValueFromItems(value);

        this.onChange(this.value?.key ?? null);
        this.onTouched();

        this.markAsTouched();
    }

    private getValueFromItems(value: K | null): SelectItem<K, V> | null {
        return this.items().find((item) => item.key === value) || null;
    }

    private markAsTouched(): void {
        if (this.touched) {
            return;
        }
        this.touched = true;
    }
}
