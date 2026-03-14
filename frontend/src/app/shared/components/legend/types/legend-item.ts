export interface LegendItem {
    key?: string;
    color: string;
    name?: string;
    translation?: string;
    subName?: string;
    value?: string | number | null;
    interactive?: boolean;
    inactive?: boolean;
}
