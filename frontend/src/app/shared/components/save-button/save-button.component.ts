import {
    ChangeDetectionStrategy, Component, input,
} from '@angular/core';
import {MatProgressSpinner} from "@angular/material/progress-spinner";
import {TranslateModule} from "@ngx-translate/core";

@Component({
    selector: 'fingather-save-button',
    templateUrl: 'save-button.component.html',
    standalone: true,
    imports: [
        TranslateModule,
        MatProgressSpinner,
    ],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class SaveButtonComponent {
    public readonly $saving = input.required<boolean>({
        alias: 'saving',
    })
    public readonly $text = input<string | null>(null, {
        alias: 'text',
    })
}
