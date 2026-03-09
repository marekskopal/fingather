export interface SelectItem<K extends PropertyKey, V> {
    key: K;
    label: V | null;
    disabled?: boolean;
    disabledLabel?: string | null;
}
