import {
    ChangeDetectionStrategy, Component, output, signal,
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
    public onKeyup$ = output<string | null>({
        alias: 'onKeyup',
    });

    protected readonly $value = signal<string | null>(null);

    protected handleKeyup(event: KeyboardEvent): void {
        const input = event.target as HTMLInputElement;
        let value: string | null = input.value;
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
