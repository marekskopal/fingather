import {
    ChangeDetectionStrategy, Component, computed, input,
} from '@angular/core';

@Component({
    selector: 'fingather-search-highlight',
    templateUrl: 'search-highlight.component.html',
    standalone: true,
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class SearchHighlightComponent {
    public readonly value = input.required<string>();
    public readonly search = input.required<string | null>();

    public readonly highlight = computed(() => {
        if (!this.search()) {
            return this.value();
        }

        const search = this.search()?.toLowerCase() ?? '';
        const value = this.value();

        const parts = value.split(new RegExp(`(${search})`, 'gi'));

        return parts.map((part) => {
            if (part.toLowerCase() === search) {
                return `<span class="highlight">${part}</span>`;
            }

            return part;
        }).join('');
    });
}
