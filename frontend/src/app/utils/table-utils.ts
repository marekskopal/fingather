export class TableUtils {
    public static getTableGridColumnMinWidth($maxNumOfDigits: number): string {
        return 91 + 5 * ($maxNumOfDigits - 1) + 'px';
    }
}
