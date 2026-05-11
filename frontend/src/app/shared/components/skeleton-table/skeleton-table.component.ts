import {ChangeDetectionStrategy, Component, computed, input} from '@angular/core';
import {TableGridDirective} from "@app/shared/directives/table-grid.directive";
import {SkeletonTableColumn} from "@app/shared/types/skeleton-table-column";
import {TableGridColumn} from "@app/shared/types/table-grid-column";
import {ScrollShadowDirective} from "@marekskopal/ng-scroll-shadow";

@Component({
    selector: 'fingather-skeleton-table',
    templateUrl: 'skeleton-table.component.html',
    imports: [TableGridDirective, ScrollShadowDirective],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class SkeletonTableComponent {
    public readonly columns = input.required<SkeletonTableColumn[]>();
    public readonly rows = input<number>(5);

    protected readonly tableGridColumns = computed<TableGridColumn[]>(() =>
        this.columns().map(({min, max}) => ({min, max})),
    );

    protected readonly rowList = computed<number[]>(() =>
        Array.from({length: this.rows()}, (_, i) => i),
    );

    protected readonly columnsWithLines = computed(() =>
        this.columns().map(column => ({
            ...column,
            extraLines: Array.from({length: Math.max(0, (column.lines ?? 1) - 1)}, (_, i) => i),
        })),
    );
}
