import {
    ChangeDetectionStrategy, Component, input,
} from '@angular/core';
import {AbstractControl} from "@angular/forms";
import {objectKeyValues} from "@app/utils/object-utils";
import {TranslateModule} from "@ngx-translate/core";

@Component({
    selector: 'fingather-input-validator',
    templateUrl: 'input-validator.component.html',
    standalone: true,
    imports: [
        TranslateModule,
    ],
    changeDetection: ChangeDetectionStrategy.OnPush,
    host: {'class': 'invalid-feedback'},
})
export class InputValidatorComponent {
    public readonly $control = input.required<AbstractControl>({
        alias: 'control',
    })
    public readonly $isSubmitted = input.required<boolean>({
        alias: 'isSubmitted',
    })
    public readonly $errorMessages = input.required<{[key: string]: string}>({
        alias: 'errorMessages',
    })
    protected readonly objectKeyValues = objectKeyValues;
}
