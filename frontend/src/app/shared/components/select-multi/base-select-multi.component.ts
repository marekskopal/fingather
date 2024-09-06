import {
    ChangeDetectionStrategy, Component, input,
} from '@angular/core';
import {ControlValueAccessor} from "@angular/forms";
import {SelectItem} from "@app/shared/types/select-item";

@Component({
    'template': '',
    standalone: true,
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export abstract class BaseSelectMultiComponent<K extends keyof any, V> implements ControlValueAccessor {
    public readonly $id = input.required<string>({
        alias: 'id',
    });
    public readonly $items = input.required<SelectItem<K, V>[]>({
        alias: 'items',
    });
    public readonly $placeholder = input<string>('', {
        alias: 'placeholder',
    });

    protected values: SelectItem<K, V>[] = [];
    private touched: boolean = false;
    protected disabled: boolean = false;

    //eslint-disable-next-line unused-imports/no-unused-vars
    private onChange = (value: K[]): void => {};
    private onTouched = (): void => {};

    public writeValue(values: K[]): void {
        this.values = this.getValuesFromItems(values);
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

    protected onToggle(key: K, event: Event): void {
        event.stopPropagation();

        if (this.hasKeyInValues(key)) {
            this.removeValueFromValues(key);
        } else {
            this.addValueIntoValues(key);
        }

        console.log(this.values);

        this.onChange(this.values.map((value) => value.key));
        this.onTouched();

        this.markAsTouched();
    }

    private getValuesFromItems(values: K[]): SelectItem<K, V>[] {
        return this.$items().filter((item) => values.includes(item.key));
    }

    protected hasKeyInValues(key: K): boolean {
        return this.values.find((value) => value.key === key) !== undefined;
    }

    private addValueIntoValues(key: K): void {
        const value = this.$items().find((item) => item.key === key);
        if (value === undefined) {
            return;
        }

        this.values = [...this.values, value];
    }

    private removeValueFromValues(key: K): void {
        this.values = this.values.filter((value) => value.key !== key);
    }

    private markAsTouched(): void {
        if (this.touched) {
            return;
        }
        this.touched = true;
    }
}
