import {
    ChangeDetectionStrategy, Component, input,
} from '@angular/core';
import {MatIcon} from "@angular/material/icon";
import {ColoredValueDirective} from "@app/shared/directives/colored-value.directive";

@Component({
    selector: 'fingather-value-icon',
    templateUrl: 'value-icon.component.html',
    imports: [
        ColoredValueDirective,
        MatIcon,
    ],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ValueIconComponent {
    public readonly $value = input.required<number | null>({
        alias: 'value',
    });
}
