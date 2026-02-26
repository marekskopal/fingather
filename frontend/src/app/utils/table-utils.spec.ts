import { TableUtils } from './table-utils';

describe('TableUtils', () => {
    describe('getTableGridColumnMinWidth', () => {
        it('returns correct width for n=1', () => {
            expect(TableUtils.getTableGridColumnMinWidth(1)).toBe('91px');
        });

        it('returns correct width for n=5', () => {
            expect(TableUtils.getTableGridColumnMinWidth(5)).toBe('111px');
        });

        it('returns correct width for n=10', () => {
            expect(TableUtils.getTableGridColumnMinWidth(10)).toBe('136px');
        });
    });
});
