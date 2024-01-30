import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { TickerData } from '@app/models';
import { environment } from '@environments/environment';
import {Observable} from 'rxjs';

@Injectable({ providedIn: 'root' })
export class TickerDataService {
    public constructor(
        private http: HttpClient
    ) {}

    public getTickerDatas(assetTickerId: number): Observable<TickerData[]> {
        return this.http.get<TickerData[]>(`${environment.apiUrl}/ticker-data/${assetTickerId}`);
    }
}
