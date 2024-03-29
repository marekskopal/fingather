import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Currency } from '@app/models';
import { CurrentUserService } from '@app/services/current-user.service';
import { environment } from '@environments/environment';
import { lastValueFrom, Observable } from 'rxjs';

@Injectable({ providedIn: 'root' })
export class CurrencyService {
    private currencies: Map<number, Currency> | null = null;

    public constructor(
        private http: HttpClient,
        private currentUserService: CurrentUserService,
    ) {}

    public getCurrencies(): Observable<Currency[]> {
        return this.http.get<Currency[]>(`${environment.apiUrl}/currency`);
    }

    public async getCurrenciesMap(): Promise<Map<number, Currency>> {
        if (this.currencies !== null) {
            return this.currencies;
        }

        const currencies = await lastValueFrom<Currency[]>(
            this.http.get<Currency[]>(`${environment.apiUrl}/currency`)
        );

        this.currencies = new Map();

        for (const currency of currencies) {
            this.currencies.set(currency.id, currency);
        }

        return this.currencies;
    }

    public async getDefaultCurrency(): Promise<Currency> {
        const currentUser = await this.currentUserService.getCurrentUser();
        const currencies = await this.getCurrenciesMap();
        const defaultCurrency = currencies.get((currentUser.defaultCurrencyId));
        if (defaultCurrency === undefined) {
            throw new Error('Default currency not exists.');
        }
        return defaultCurrency;
    }
}
