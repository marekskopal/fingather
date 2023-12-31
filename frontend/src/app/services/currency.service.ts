import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';

import { environment } from '@environments/environment';
import { Currency } from '@app/models';
import {CurrentUserService} from "@app/services/current-user.service";

@Injectable({ providedIn: 'root' })
export class CurrencyService {
    private currencies: Map<number,Currency>|null = null;

    public constructor(
        private http: HttpClient,
        private currentUserService: CurrentUserService,
    ) {}

    public async getCurrencies(): Promise<Map<number,Currency>> {
        if (this.currencies !== null) {
            return this.currencies;
        }

        const currencies = await this.http.get<Currency[]>(`${environment.apiUrl}/currency`).toPromise();

        this.currencies = new Map();
        for (const currency of currencies) {
            this.currencies.set(currency.id, currency);
        }

        return this.currencies;
    }

    public async getDefaultCurrency(): Promise<Currency> {
        const currentUser = await this.currentUserService.getCurrentUser();
        const currencies = await this.getCurrencies();
        const defaultCurrency = currencies.get((currentUser.defaultCurrencyId));
        if (defaultCurrency === undefined) {
            throw "Default currency not exists."
        }
        return defaultCurrency;
    }
}
