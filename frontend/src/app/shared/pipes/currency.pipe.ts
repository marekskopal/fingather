import { Pipe, PipeTransform } from '@angular/core';
import {CurrencyService} from "@app/services";

@Pipe({
    name: 'currency'
})
export class CurrencyPipe implements PipeTransform {
    public constructor(
        private currencyService: CurrencyService,
    ) {
    }

    public async transform(value: string|null, currencyId: number): Promise<string> {
        if (value === null) {
            return '';
        }

        const currencies = await this.currencyService.getCurrencies();

        return value + currencies.get(currencyId)?.symbol;
    }
}
