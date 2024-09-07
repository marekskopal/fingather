import {inject, Pipe, PipeTransform} from '@angular/core';
import { CurrencyService } from '@app/services';

@Pipe({
    name: 'currencyCode',
    standalone: true,
})
export class CurrencyCodePipe implements PipeTransform {
    private readonly currencyService = inject(CurrencyService);

    public async transform(currencyId: number): Promise<string> {
        const currencies = await this.currencyService.getCurrenciesMap();
        const currency = currencies.get(currencyId);
        if (currency === undefined) {
            return '';
        }

        return currency.code;
    }
}
