import { ChartUtils } from './chart-utils';
import { ColorEnum } from './enum/color-enum';

describe('ChartUtils', () => {
    describe('colors', () => {
        it('returns single color for count=1', () => {
            expect(ChartUtils.colors(1)).toEqual([ColorEnum.colorChart2]);
        });

        it('returns two colors for count=2', () => {
            expect(ChartUtils.colors(2)).toEqual([ColorEnum.colorChart2, ColorEnum.colorChart5]);
        });

        it('returns three colors for count=3', () => {
            expect(ChartUtils.colors(3)).toEqual([ColorEnum.colorChart2, ColorEnum.colorChart5, ColorEnum.colorChart4]);
        });

        it('returns four colors for count=4 (default case)', () => {
            const result = ChartUtils.colors(4);
            expect(result).toHaveLength(4);
            expect(result[0]).toBe(ColorEnum.colorChart1);
            expect(result[3]).toBe(ColorEnum.colorChart4);
        });

        it('returns five colors for count=5 (default)', () => {
            const result = ChartUtils.colors(5);
            expect(result).toHaveLength(5);
            expect(result[0]).toBe(ColorEnum.colorChart1);
            expect(result[4]).toBe(ColorEnum.colorChart5);
        });
    });

    describe('getColor', () => {
        it('returns first color for index 0', () => {
            expect(ChartUtils.getColor(0)).toBe(ChartUtils.colors()[0]);
        });

        it('wraps around with modulo for index equal to colors length', () => {
            const length = ChartUtils.colors().length;
            expect(ChartUtils.getColor(length)).toBe(ChartUtils.getColor(0));
        });

        it('returns correct color for index 7 with modulo wrapping', () => {
            const length = ChartUtils.colors().length;
            expect(ChartUtils.getColor(7)).toBe(ChartUtils.colors()[7 % length]);
        });
    });

    describe('gradientFill', () => {
        it('returns an object with type gradient', () => {
            const fill = ChartUtils.gradientFill();
            expect(fill.type).toBe('gradient');
        });

        it('returns a gradient with expected shape', () => {
            const fill = ChartUtils.gradientFill();
            expect(fill.gradient).toBeDefined();
            expect(fill.gradient?.shade).toBe('dark');
        });
    });

    describe('theme', () => {
        it('returns dark mode theme', () => {
            expect(ChartUtils.theme()).toEqual({ mode: 'dark' });
        });
    });
});
