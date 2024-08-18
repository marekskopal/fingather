import {
    ChangeDetectionStrategy, Component,
} from '@angular/core';
import {NG_VALUE_ACCESSOR} from "@angular/forms";
import {BaseSelectComponent} from "@app/shared/components/select/base-select.component";

@Component({
    selector: 'fingather-select',
    templateUrl: 'select.component.html',
    changeDetection: ChangeDetectionStrategy.OnPush,
    providers: [
        {
            provide: NG_VALUE_ACCESSOR,
            multi:true,
            useExisting: SelectComponent
        }
    ]
})
export class SelectComponent extends BaseSelectComponent<string | number, string> {
}
