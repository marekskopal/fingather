import {
    ChangeDetectionStrategy, Component, input, output,
} from '@angular/core';
import {MatIcon} from "@angular/material/icon";
import {MatProgressSpinner} from "@angular/material/progress-spinner";
import { TranslatePipe} from "@ngx-translate/core";

@Component({
    selector: 'fingather-save-button',
    templateUrl: 'save-button.component.html',
    standalone: true,
    imports: [
        TranslatePipe,
        MatProgressSpinner,
        MatIcon,
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
    public readonly $icon = input<string | null>(null, {
        alias: 'icon',
    })
    public readonly onClick$ = output<Event>({
        alias: 'onClick',
    });

    protected handleOnClick(event: Event): void {
        this.onClick$.emit(event);
    }
}
