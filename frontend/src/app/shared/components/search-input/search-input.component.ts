import {
    ChangeDetectionStrategy, Component, input, output, signal,
} from '@angular/core';
import {MatIcon} from "@angular/material/icon";

@Component({
    selector: 'fingather-search-input',
    templateUrl: 'search-input.component.html',
    standalone: true,
    imports: [
        MatIcon
    ],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class SearchInputComponent {
    public readonly $id = input.required<string>({
        alias: 'id',
    });
    public onKeyup$ = output<string | null>({
        alias: 'onKeyup',
    });
    public readonly $showClearButton = input<boolean>(true, {
        alias: 'showClearButton',
    });

    protected readonly $value = signal<string | null>(null);

    protected handleKeyup(event: KeyboardEvent): void {
        const inputElement = event.target as HTMLInputElement;
        let value: string | null = inputElement.value;
        if (value.length === 0) {
            value = null;
        }

        this.$value.set(value);

        this.onKeyup$.emit(this.$value());
    }

    protected clearSearch(): void {
        this.$value.set(null);

        this.onKeyup$.emit(this.$value());
    }
}
