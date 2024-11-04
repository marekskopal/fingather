import {
    ChangeDetectionStrategy, Component, computed, input, output, signal
} from '@angular/core';
import {MatIcon} from "@angular/material/icon";
import {NgbDropdown, NgbDropdownItem, NgbDropdownMenu, NgbDropdownToggle} from "@ng-bootstrap/ng-bootstrap";
import { TranslatePipe} from "@ngx-translate/core";

@Component({
    selector: 'fingather-pagination',
    templateUrl: 'pagination.component.html',
    standalone: true,
    changeDetection: ChangeDetectionStrategy.OnPush,
    imports: [
        TranslatePipe,
        MatIcon,
        NgbDropdown,
        NgbDropdownToggle,
        NgbDropdownMenu,
        NgbDropdownItem
    ]
})
export class PaginationComponent {
    public readonly $totalItems = input.required<number>({
        alias: 'totalItems',
    });
    public readonly $defaultPageSize = input<number>(50, {
        alias: 'defaultPageSize',
    });
    public readonly onSelectPage$ = output<number>({
        alias: 'onSelectPage',
    });
    public readonly $maxPages = input<number>(10, {
        alias: 'maxPages',
    });
    public readonly $delimiter = input<string>('...', {
        alias: 'delimiter',
    });
    public readonly $pageSizeOptions = input<number[]>([25, 50, 100, 200], {
        alias: 'pageSizeOptions',
    });
    public readonly onChangePageSize$ = output<number>({
        alias: 'onChangePageSize',
    });

    protected readonly $pageSize = signal<number>(this.$defaultPageSize());
    protected readonly $currentPage = signal<number>(1);
    protected readonly $pagesCount = computed<number>(() => Math.ceil(this.$totalItems() / this.$pageSize()));

    protected readonly $pages = computed<(number|string)[]>(() => {
        return this.getPaginationPages(
            this.$totalItems(),
            this.$pageSize(),
            this.$maxPages(),
            this.$currentPage()
        );
    });

    protected onSelectPage(page: number | string): void {
        if (typeof page === 'string') {
            return;
        }

        this.$currentPage.set(page);
        this.onSelectPage$.emit(this.$currentPage());
    }

    protected isDelimiterPage(page: number | string): boolean {
        return page === this.$delimiter();
    }

    protected async changePageSize(pageSize: number): Promise<void> {
        this.$pageSize.set(pageSize);
        this.onChangePageSize$.emit(pageSize);
    }

    private getPaginationPages(
        totalItems: number,
        itemsPerPage: number,
        maxPages: number,
        currentPage: number
    ): (number | string)[] {
        const totalPages = Math.ceil(totalItems / itemsPerPage);
        const pages: (number | string)[] = [];

        let startPage = 1;
        let endPage = totalPages;

        if (totalPages > maxPages) {
            startPage = Math.max(1, currentPage - Math.floor(maxPages / 2));
            endPage = startPage + maxPages - 1;

            if (endPage > totalPages) {
                endPage = totalPages;
                startPage = endPage - maxPages + 1;
            }
        }

        for (let i = startPage; i <= endPage; i++) {
            pages.push(i);
        }

        if (startPage > 1) {
            pages.unshift('...');
            pages.unshift(1);
        }

        if (endPage < totalPages) {
            pages.push('...');
            pages.push(totalPages);
        }

        return pages;
    }
}
