import {
    ChangeDetectionStrategy, Component,
} from '@angular/core';
import {NG_VALUE_ACCESSOR} from "@angular/forms";
import {BaseSelectComponent} from "@app/shared/components/select/base-select.component";
import {NgbDropdown, NgbDropdownItem, NgbDropdownMenu, NgbDropdownToggle} from "@ng-bootstrap/ng-bootstrap";

@Component({
    selector: 'fingather-select',
    templateUrl: 'select.component.html',
    imports: [
        NgbDropdown,
        NgbDropdownToggle,
        NgbDropdownMenu,
        NgbDropdownItem,
    ],
    changeDetection: ChangeDetectionStrategy.OnPush,
    providers: [
        {
            provide: NG_VALUE_ACCESSOR,
            multi: true,
            useExisting: SelectComponent,
        },
    ],
})
export class SelectComponent extends BaseSelectComponent<string | number, string> {
}
