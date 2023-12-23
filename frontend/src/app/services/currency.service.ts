import { Injectable } from '@angular/core';
import { Router } from '@angular/router';
import { HttpClient, HttpParams } from '@angular/common/http';
import { map } from 'rxjs/operators';

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
