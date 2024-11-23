import {
    ChangeDetectionStrategy, Component,
} from '@angular/core';
import {NG_VALUE_ACCESSOR} from "@angular/forms";
import {TransactionActionType} from "@app/models";
import {BaseSelectComponent} from "@app/shared/components/select/base-select.component";
import {TagComponent} from "@app/shared/components/tag/tag.component";
import {NgbDropdown, NgbDropdownItem, NgbDropdownMenu, NgbDropdownToggle} from "@ng-bootstrap/ng-bootstrap";

@Component({
    selector: 'fingather-type-select',
    templateUrl: 'type-select.component.html',
    imports: [
        NgbDropdown,
        NgbDropdownToggle,
        NgbDropdownMenu,
        NgbDropdownItem,
        TagComponent,
    ],
    changeDetection: ChangeDetectionStrategy.OnPush,
    providers: [
        {
            provide: NG_VALUE_ACCESSOR,
            multi: true,
            useExisting: TypeSelectComponent,
        },
    ],
})
export class TypeSelectComponent extends BaseSelectComponent<TransactionActionType, TransactionActionType> {
    protected readonly TransactionActionType = TransactionActionType;
}
