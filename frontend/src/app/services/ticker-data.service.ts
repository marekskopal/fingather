import { Injectable } from '@angular/core';
import { Router } from '@angular/router';
import { HttpClient } from '@angular/common/http';
import { BehaviorSubject, Observable } from 'rxjs';
import { map } from 'rxjs/operators';

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
