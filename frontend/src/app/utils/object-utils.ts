export function objectValues<T extends object>(target: T | null | undefined): T[keyof T][] {
    if (!target) {
        return [];
    }
    return Object.values(target) as T[keyof T][];
}

export interface IKeyValue<T, K extends keyof T = keyof T> {
    key: K;
    value: T[K];
}

export function objectEntries<T extends object>(
    target: T | null | undefined,
): [keyof T, T[keyof T]][] {
    if (!target) {
        return [];
    }
    return Object.entries(target) as [keyof T, T[keyof T]][];
}

export function objectKeyValues<T extends object>(target: T | null | undefined): IKeyValue<T>[] {
    return objectEntries(target).map(([key, value]) => ({key, value}));
}
