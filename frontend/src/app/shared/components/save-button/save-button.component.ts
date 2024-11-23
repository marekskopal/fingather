import {
    ChangeDetectionStrategy, Component, input, output,
} from '@angular/core';
import {MatIcon} from "@angular/material/icon";
import {MatProgressSpinner} from "@angular/material/progress-spinner";
import { TranslatePipe} from "@ngx-translate/core";

@Component({
    selector: 'fingather-save-button',
    templateUrl: 'save-button.component.html',
    imports: [
        TranslatePipe,
        MatProgressSpinner,
        MatIcon,
    ],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class SaveButtonComponent {
    public readonly saving = input.required<boolean>();
    public readonly text = input<string | null>(null);
    public readonly icon = input<string | null>(null);
    public readonly afterClick = output<Event>();

    protected handleOnClick(event: Event): void {
        this.afterClick.emit(event);
    }
}
