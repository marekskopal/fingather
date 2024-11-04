import {
    ChangeDetectionStrategy, Component,
} from '@angular/core';
import {NG_VALUE_ACCESSOR} from "@angular/forms";
import {MatIcon} from "@angular/material/icon";
import {BaseSelectMultiComponent} from "@app/shared/components/select-multi/base-select-multi.component";
import {NgbDropdown, NgbDropdownItem, NgbDropdownMenu, NgbDropdownToggle} from "@ng-bootstrap/ng-bootstrap";
import { TranslatePipe} from "@ngx-translate/core";

@Component({
    selector: 'fingather-select-multi',
    templateUrl: 'select-multi.component.html',
    standalone: true,
    imports: [
        NgbDropdown,
        NgbDropdownToggle,
        NgbDropdownMenu,
        NgbDropdownItem,
        MatIcon,
        TranslatePipe,
        MatIcon
    ],
    changeDetection: ChangeDetectionStrategy.OnPush,
    providers: [
        {
            provide: NG_VALUE_ACCESSOR,
            multi: true,
            useExisting: SelectMultiComponent
        }
    ]
})
export class SelectMultiComponent extends BaseSelectMultiComponent<string | number, string> {
}
