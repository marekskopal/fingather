import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';

import { environment } from '@environments/environment';
import { TickerData } from '@app/models';
import {Observable} from "rxjs";

@Injectable({ providedIn: 'root' })
export class TickerDataService {
    public constructor(
        private http: HttpClient
    ) {}

    public findLastYear(assetTickerId: number): Observable<TickerData[]> {
        return this.http.get<TickerData[]>(`${environment.apiUrl}/ticker-data/${assetTickerId}`);
    }
}
