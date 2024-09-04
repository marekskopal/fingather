import {inject, Pipe, PipeTransform} from '@angular/core';
import { CurrencyService } from '@app/services';

@Pipe({
    name: 'currency',
    standalone: true,
})
export class CurrencyPipe implements PipeTransform {
    private readonly currencyService = inject(CurrencyService);

    public async transform(value: string | null, currencyId: number): Promise<string> {
        if (value === null) {
            return '';
        }

        const currencies = await this.currencyService.getCurrenciesMap();
        const currency = currencies.get(currencyId);

        let symbol = '';
        if (currency !== undefined) {
            symbol = currency.symbol;
        }

        return value + symbol;
    }
}
