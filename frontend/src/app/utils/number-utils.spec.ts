import { NumberUtils } from './number-utils';

describe('NumberUtils', () => {
    describe('numberOfDigits', () => {
        it('returns 1 for a single-digit positive number', () => {
            expect(NumberUtils.numberOfDigits(7)).toBe(1);
        });

        it('returns 2 for a two-digit positive number', () => {
            expect(NumberUtils.numberOfDigits(42)).toBe(2);
        });

        it('returns 3 for a three-digit positive number', () => {
            expect(NumberUtils.numberOfDigits(999)).toBe(3);
        });

        it('returns 1 for a negative single-digit number', () => {
            expect(NumberUtils.numberOfDigits(-5)).toBe(1);
        });

        it('returns 2 for a negative two-digit number', () => {
            expect(NumberUtils.numberOfDigits(-25)).toBe(2);
        });

        it('returns 1 for zero', () => {
            expect(NumberUtils.numberOfDigits(0)).toBe(1);
        });
    });
});
