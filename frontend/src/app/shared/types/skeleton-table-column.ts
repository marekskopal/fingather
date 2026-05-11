import {TableGridColumn} from "@app/shared/types/table-grid-column";

export interface SkeletonTableColumn extends TableGridColumn {
    align?: 'start' | 'end';
    hasAvatar?: boolean;
    isActions?: boolean;
    lines?: number;
}
