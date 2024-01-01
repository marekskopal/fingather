import { Injectable } from '@angular/core';
import {HttpClient, HttpParams} from '@angular/common/http';

import { environment } from '@environments/environment';
import { Ticker } from '@app/models';
import {Observable} from "rxjs";

@Injectable({ providedIn: 'root' })
export class TickerService {
    public constructor(
        private http: HttpClient
    ) {}

    public getTicker(ticker: string): Observable<Ticker> {
        const params = new HttpParams().set('ticker', ticker);

        return this.http.get<Ticker>(`${environment.apiUrl}/ticker`, {params});
    }
}
