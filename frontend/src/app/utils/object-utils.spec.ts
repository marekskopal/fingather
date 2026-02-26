import { objectEntries, objectKeyValues, objectValues } from './object-utils';

describe('objectValues', () => {
    it('returns empty array for null', () => {
        expect(objectValues(null)).toEqual([]);
    });

    it('returns empty array for undefined', () => {
        expect(objectValues(undefined)).toEqual([]);
    });

    it('returns values for a valid object', () => {
        const result = objectValues({ a: 1, b: 2, c: 3 });
        expect(result).toEqual([1, 2, 3]);
    });
});

describe('objectEntries', () => {
    it('returns empty array for null', () => {
        expect(objectEntries(null)).toEqual([]);
    });

    it('returns empty array for undefined', () => {
        expect(objectEntries(undefined)).toEqual([]);
    });

    it('returns key/value pairs for a valid object', () => {
        const result = objectEntries({ x: 10, y: 20 });
        expect(result).toEqual([['x', 10], ['y', 20]]);
    });
});

describe('objectKeyValues', () => {
    it('returns empty array for null', () => {
        expect(objectKeyValues(null)).toEqual([]);
    });

    it('returns empty array for undefined', () => {
        expect(objectKeyValues(undefined)).toEqual([]);
    });

    it('returns IKeyValue objects with correct key and value', () => {
        const result = objectKeyValues({ foo: 'bar', baz: 42 });
        expect(result).toEqual([
            { key: 'foo', value: 'bar' },
            { key: 'baz', value: 42 },
        ]);
    });
});
