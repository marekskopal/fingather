import { Injectable } from '@angular/core';
import {HttpClient, HttpParams} from '@angular/common/http';

import { environment } from '@environments/environment';
import { Ticker } from '@app/models';

@Injectable({ providedIn: 'root' })
export class AssetTickerService {
    public constructor(
        private http: HttpClient
    ) {}

    public getByTicker(ticker: string) {
        const params = new HttpParams().set('ticker', ticker);

        return this.http.get<Ticker>(`${environment.apiUrl}/assetticker`, {params});
    }
}
