export interface SelectItem<K extends keyof any, V> {
    key: K;
    label: V | null;
}
