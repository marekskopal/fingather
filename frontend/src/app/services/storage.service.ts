import { Injectable } from '@angular/core';

@Injectable({ providedIn: 'root' })
export class StorageService {
    public get<T>(key: string): T | null {
        const value = localStorage.getItem(key);
        if (value === null) {
            return null;
        }

        try {
            return JSON.parse(value) as T;
        } catch {
            return null;
        }
    }

    public set<T>(key: string, value: T): void {
        localStorage.setItem(key, JSON.stringify(value));
    }

    public remove(key: string): void {
        localStorage.removeItem(key);
    }
}
