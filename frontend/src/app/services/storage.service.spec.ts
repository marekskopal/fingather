import { TestBed } from '@angular/core/testing';

import { StorageService } from './storage.service';

describe('StorageService', () => {
    let service: StorageService;

    beforeEach(() => {
        localStorage.clear();
        TestBed.configureTestingModule({ providers: [StorageService] });
        service = TestBed.inject(StorageService);
    });

    afterEach(() => {
        localStorage.clear();
    });

    describe('set / get', () => {
        it('stores and retrieves an object', () => {
            service.set('key', { foo: 'bar' });
            expect(service.get<{ foo: string }>('key')).toEqual({ foo: 'bar' });
        });

        it('stores and retrieves a string', () => {
            service.set('lang', 'en');
            expect(service.get<string>('lang')).toBe('en');
        });

        it('returns null for a missing key', () => {
            expect(service.get('missing')).toBeNull();
        });

        it('returns null for malformed JSON', () => {
            localStorage.setItem('bad', '{not json}');
            expect(service.get('bad')).toBeNull();
        });
    });

    describe('remove', () => {
        it('removes the key', () => {
            service.set('key', 'value');
            service.remove('key');
            expect(service.get('key')).toBeNull();
        });

        it('does not throw when key does not exist', () => {
            expect(() => service.remove('nope')).not.toThrow();
        });
    });
});
