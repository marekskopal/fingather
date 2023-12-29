import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';

import { environment } from '@environments/environment';
import { tickerData } from '@app/models';

@Injectable({ providedIn: 'root' })
export class TickerDataService {
    constructor(
        private http: HttpClient
    ) {}

    findLastYear(assetTickerId: number) {
        return this.http.get<tickerData[]>(`${environment.apiUrl}/ticker-data/${assetTickerId}`);
    }
}
