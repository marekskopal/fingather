import {
    ChangeDetectionStrategy, Component, input, output, signal,
} from '@angular/core';
import {MatIcon} from "@angular/material/icon";

@Component({
    selector: 'fingather-search-input',
    templateUrl: 'search-input.component.html',
    imports: [
        MatIcon,
    ],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class SearchInputComponent {
    public readonly id = input.required<string>();
    public afterKeyup = output<string | null>();
    public readonly showClearButton = input<boolean>(true);

    protected readonly value = signal<string | null>(null);

    protected handleKeyup(event: KeyboardEvent): void {
        const inputElement = event.target as HTMLInputElement;
        let value: string | null = inputElement.value;
        if (value.length === 0) {
            value = null;
        }

        this.value.set(value);

        this.afterKeyup.emit(this.value());
    }

    protected clearSearch(): void {
        this.value.set(null);

        this.afterKeyup.emit(this.value());
    }
}
