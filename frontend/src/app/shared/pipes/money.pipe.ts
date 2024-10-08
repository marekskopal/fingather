import {inject, Pipe, PipeTransform} from '@angular/core';
import { CurrencyService } from '@app/services';

@Pipe({
    name: 'money',
    standalone: true,
})
export class MoneyPipe implements PipeTransform {
    private readonly currencyService = inject(CurrencyService);

    public async transform(value: string | null, currencyId: number): Promise<string> {
        if (value === null) {
            return '';
        }

        const currencies = await this.currencyService.getCurrenciesMap();
        const currency = currencies.get(currencyId);
        if (currency === undefined) {
            return value;
        }

        return value + currency.symbol;
    }
}
