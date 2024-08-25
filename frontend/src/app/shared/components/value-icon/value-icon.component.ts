import {
    ChangeDetectionStrategy, Component, input
} from '@angular/core';

@Component({
    selector: 'fingather-value-icon',
    templateUrl: 'value-icon.component.html',
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ValueIconComponent {
    public readonly $value = input.required<number | null>({
        alias: 'value',
    });
}
