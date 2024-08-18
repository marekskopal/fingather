import {
    ChangeDetectionStrategy, Component, computed, inject, input, output, signal,
} from '@angular/core';
import { AlertService } from '@app/services';
import { ConfirmDialogService } from '@app/services/confirm-dialog.service';
import {ControlValueAccessor, NG_VALUE_ACCESSOR} from "@angular/forms";
import {SelectItem} from "@app/shared/types/select-item";

@Component({
    'template': '',
})
export abstract class BaseSelectComponent<K extends keyof any, V> implements ControlValueAccessor {
    public readonly $id = input.required<string>({
        alias: 'id',
    });
    public readonly $items = input.required<SelectItem<K, V>[]>({
        alias: 'items',
    });
    public readonly $placeholder = input<string>('', {
        alias: 'placeholder',
    });

    protected value: SelectItem<K, V> | null = null;
    private touched: boolean = false;
    protected disabled: boolean = false;

    private onChange = (value: K | null) => {};
    private onTouched = () => {};

    public writeValue(value: K | null) {
        this.value = this.getValueFromItems(value);
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

    protected onSelect(value: K | null): void {
        this.value = this.getValueFromItems(value);

        this.onChange(this.value?.key ?? null);
        this.onTouched();

        this.markAsTouched();
    }

    private getValueFromItems(value: K | null): SelectItem<K, V> | null {
        return this.$items().find((item) => item.key === value) || null;
    }

    private markAsTouched(): void {
        if (this.touched) {
            return;
        }
        this.touched = true;
    }
}
