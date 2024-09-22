export class NumberUtils {
    public static numberOfDigits(x: number): number {
        return (Math.log10((x ^ (x >> 31)) - (x >> 31)) | 0) + 1;
    }
}
