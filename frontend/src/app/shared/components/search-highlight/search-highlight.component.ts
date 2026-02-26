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
            return this.escapeHtml(this.value());
        }

        const search = this.search()?.toLowerCase() ?? '';
        const value = this.value();

        const escapedSearch = search.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        const parts = value.split(new RegExp(`(${escapedSearch})`, 'gi'));

        return parts.map((part) => {
            if (part.toLowerCase() === search) {
                return `<span class="highlight">${this.escapeHtml(part)}</span>`;
            }

            return this.escapeHtml(part);
        }).join('');
    });

    private escapeHtml(text: string): string {
        return text
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#x27;');
    }
}
