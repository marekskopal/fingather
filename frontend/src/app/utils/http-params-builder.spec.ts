import { buildHttpParams } from './http-params-builder';

describe('buildHttpParams', () => {
    it('returns empty HttpParams when all values are null or undefined', () => {
        const params = buildHttpParams({ a: null, b: undefined });
        expect(params.keys()).toHaveLength(0);
    });

    it('sets string values', () => {
        const params = buildHttpParams({ search: 'foo' });
        expect(params.get('search')).toBe('foo');
    });

    it('sets number values', () => {
        const params = buildHttpParams({ limit: 10, offset: 20 });
        expect(params.get('limit')).toBe('10');
        expect(params.get('offset')).toBe('20');
    });

    it('sets boolean values', () => {
        const params = buildHttpParams({ withCurrentValue: true });
        expect(params.get('withCurrentValue')).toBe('true');
    });

    it('skips null values but includes present ones', () => {
        const params = buildHttpParams({ limit: 10, offset: null, search: 'bar' });
        expect(params.get('limit')).toBe('10');
        expect(params.has('offset')).toBe(false);
        expect(params.get('search')).toBe('bar');
    });

    it('skips undefined values but includes present ones', () => {
        const params = buildHttpParams({ horizonYears: undefined, withCurrentValue: false });
        expect(params.has('horizonYears')).toBe(false);
        expect(params.get('withCurrentValue')).toBe('false');
    });

    it('handles zero and empty string as valid values', () => {
        const params = buildHttpParams({ offset: 0, search: '' });
        expect(params.get('offset')).toBe('0');
        expect(params.get('search')).toBe('');
    });
});
