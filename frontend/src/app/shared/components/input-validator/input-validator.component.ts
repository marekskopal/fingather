import {
    ChangeDetectionStrategy, Component, input,
} from '@angular/core';
import {AbstractControl} from "@angular/forms";
import {objectKeyValues} from "@app/utils/object-utils";
import { TranslatePipe} from "@ngx-translate/core";

@Component({
    selector: 'fingather-input-validator',
    templateUrl: 'input-validator.component.html',
    imports: [
        TranslatePipe,
    ],
    changeDetection: ChangeDetectionStrategy.OnPush,
    host: { 'class': 'invalid-feedback' },
})
export class InputValidatorComponent {
    public readonly control = input.required<AbstractControl>();
    public readonly isSubmitted = input.required<boolean>();
    public readonly errorMessages = input.required<{[key: string]: string}>();
    protected readonly objectKeyValues = objectKeyValues;
}
