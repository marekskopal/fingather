import {
    ChangeDetectionStrategy, Component, input,
} from '@angular/core';
import {ControlValueAccessor, NG_VALUE_ACCESSOR} from "@angular/forms";
import {MatIcon} from "@angular/material/icon";
import {Color} from "@app/shared/types/color";
import {SelectItem} from "@app/shared/types/select-item";
import {ColorEnum} from "@app/utils/enum/color-enum";
import {NgbDropdown, NgbDropdownItem, NgbDropdownMenu, NgbDropdownToggle} from "@ng-bootstrap/ng-bootstrap";
import { TranslatePipe} from "@ngx-translate/core";

@Component({
    selector: 'fingather-color-picker',
    templateUrl: 'color-picker.component.html',
    imports: [
        NgbDropdown,
        NgbDropdownToggle,
        NgbDropdownMenu,
        NgbDropdownItem,
        TranslatePipe,
        MatIcon,
    ],
    changeDetection: ChangeDetectionStrategy.OnPush,
    providers: [
        {
            provide: NG_VALUE_ACCESSOR,
            multi: true,
            useExisting: ColorPickerComponent,
        },
    ],
})
export class ColorPickerComponent implements ControlValueAccessor {
    public readonly id = input.required<string>();
    public readonly items = input<SelectItem<Color, Color>[]>([
        {key: ColorEnum.colorPicker1, label: ColorEnum.colorPicker1},
        {key: ColorEnum.colorPicker2, label: ColorEnum.colorPicker2},
        {key: ColorEnum.colorPicker3, label: ColorEnum.colorPicker3},
        {key: ColorEnum.colorPicker4, label: ColorEnum.colorPicker4},
        {key: ColorEnum.colorPicker5, label: ColorEnum.colorPicker5},
        {key: ColorEnum.colorPicker6, label: ColorEnum.colorPicker6},
        {key: ColorEnum.colorPicker7, label: ColorEnum.colorPicker7},
    ]);
    public readonly placeholder = input<string>('');

    protected value: Color | null = null;
    private touched: boolean = false;
    protected disabled: boolean = false;

    //eslint-disable-next-line unused-imports/no-unused-vars
    private onChange = (value: Color | null): void => {};
    private onTouched = (): void => {};

    public writeValue(value: Color | null): void {
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

    protected onSelect(value: Color | null): void {
        const valueFromItems = this.getValueFromItems(value)?.key ?? null;
        this.value = valueFromItems;

        this.onChange(valueFromItems);
        this.onTouched();

        this.markAsTouched();
    }

    private getValueFromItems(value: Color | null): SelectItem<Color, Color> | null {
        return this.items().find((item) => item.key === value) || null;
    }

    private markAsTouched(): void {
        if (this.touched) {
            return;
        }
        this.touched = true;
    }

    protected onChangeCustom(event: Event): void {
        const inputElement = event.target as HTMLInputElement;
        const value = inputElement.value as Color;

        this.value = value;

        this.onChange(value);
        this.onTouched();

        this.markAsTouched();
    }
}
