import { HttpClient } from '@angular/common/http';
import {inject, Injectable} from '@angular/core';
import { Currency } from '@app/models';
import { PortfolioService } from '@app/services/portfolio.service';
import { environment } from '@environments/environment';
import { firstValueFrom } from 'rxjs';

@Injectable({ providedIn: 'root' })
export class CurrencyService {
    private readonly http = inject(HttpClient);
    private readonly portfolioService = inject(PortfolioService);

    private currencies: Map<number, Currency> | null = null;

    public getCurrencies(): Promise<Currency[]> {
        return firstValueFrom<Currency[]>(this.http.get<Currency[]>(`${environment.apiUrl}/currency`));
    }

    public async getCurrenciesMap(): Promise<Map<number, Currency>> {
        if (this.currencies !== null) {
            return this.currencies;
        }

        const currencies = await this.getCurrencies();
        this.currencies = new Map();

        for (const currency of currencies) {
            this.currencies.set(currency.id, currency);
        }

        return this.currencies;
    }

    public async getDefaultCurrency(): Promise<Currency> {
        const currentPortfolio = await this.portfolioService.getCurrentPortfolio();
        const currencies = await this.getCurrenciesMap();
        const defaultCurrency = currencies.get((currentPortfolio.currencyId));
        if (defaultCurrency === undefined) {
            throw new Error('Default currency not exists.');
        }
        return defaultCurrency;
    }
}
