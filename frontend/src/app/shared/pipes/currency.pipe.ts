import { Pipe, PipeTransform } from '@angular/core';
import {Currency} from "@app/models";

@Pipe({
    name: 'currency'
})
export class CurrencyPipe implements PipeTransform {
     public transform(value: string|null, currencyId: number, currencies: Map<number, Currency>): string {
        if (value === null) {
            return '';
        }

        return value + currencies.get(currencyId)?.symbol;
    }
}
