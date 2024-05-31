import {
    ChangeDetectionStrategy, Component, computed, input, output, signal
} from '@angular/core';

@Component({
    selector: 'fingather-pagination',
    templateUrl: 'pagination.component.html',
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class PaginationComponent {
    public readonly $itemsSize = input.required<number>({
        alias: 'itemsSize',
    });
    public readonly $pageSize = input.required<number>({
        alias: 'pageSize',
    });
    public readonly onSelectPage$ = output<number>({
        alias: 'onSelectPage',
    });

    protected readonly $page = signal<number>(1);
    protected readonly $pagesCount = computed<number>(() => Math.ceil(this.$itemsSize() / this.$pageSize()));

    protected readonly $pages = computed<number[]>(() => {
        const pages = [];

        const pagesCount = this.$pagesCount();

        for (let i = 1; i <= pagesCount; i += 1) {
            pages.push(i);
        }

        return pages;
    });

    protected onSelectPage(page: number): void {
        this.$page.set(page);
        this.onSelectPage$.emit(this.$page());
    }
}
