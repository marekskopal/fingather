import {
    ChangeDetectionStrategy, Component,
} from '@angular/core';
import {NG_VALUE_ACCESSOR} from "@angular/forms";
import {TransactionActionType} from "@app/models";
import {BaseSelectComponent} from "@app/shared/components/select/base-select.component";

@Component({
    selector: 'fingather-type-select',
    templateUrl: 'type-select.component.html',
    changeDetection: ChangeDetectionStrategy.OnPush,
    providers: [
        {
            provide: NG_VALUE_ACCESSOR,
            multi:true,
            useExisting: TypeSelectComponent
        }
    ]
})
export class TypeSelectComponent extends BaseSelectComponent<TransactionActionType, TransactionActionType> {
    protected readonly TransactionActionType = TransactionActionType;
}
