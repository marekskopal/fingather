import { Pipe, PipeTransform } from '@angular/core';
import { CurrencyService } from '@app/services';

@Pipe({
    name: 'fileSize'
})
export class FileSizePipe implements PipeTransform {
    private readonly units = ['B', 'KB', 'MB', 'GB', 'TB'] as const;

    public transform(value: number | null): string {
        if (value == null || value === 0) {
            return '0 Bytes';
        }

        const i = Math.floor(Math.log(value) / Math.log(1024));
        return `${parseFloat((value / Math.pow(1024, i)).toFixed(2))} ${this.units[i]}`;
    }
}
