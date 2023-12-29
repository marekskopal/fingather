import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';

import { environment } from '@environments/environment';
import { Currency } from '@app/models';

@Injectable({ providedIn: 'root' })
export class CurrencyService {
    constructor(
        private http: HttpClient
    ) {}

    findAll() {
        return this.http.get<Currency[]>(`${environment.apiUrl}/currency`);
    }

    getById(id: string) {
        return this.http.get<Currency>(`${environment.apiUrl}/currency/${id}`);
    }
}
