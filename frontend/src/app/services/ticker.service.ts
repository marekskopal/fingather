import { HttpClient, HttpParams } from '@angular/common/http';
import {inject, Injectable} from '@angular/core';
import { Ticker } from '@app/models';
import { environment } from '@environments/environment';
import {firstValueFrom} from 'rxjs';

@Injectable({ providedIn: 'root' })
export class TickerService {
    private readonly http = inject(HttpClient);

    public getTickers(
        search: string | null = null,
        limit: number | null = null,
        offset: number | null = null,
    ): Promise<Ticker[]> {
        let params = new HttpParams();

        if (search !== null) {
            params = params.set('search', search);
        }

        if (limit !== null) {
            params = params.set('limit', limit);
        }

        if (offset !== null) {
            params = params.set('offset', offset);
        }

        return firstValueFrom(this.http.get<Ticker[]>(`${environment.apiUrl}/tickers`, { params }));
    }

    public getTickersMostUsed(
        limit: number | null = null,
        offset: number | null = null,
    ): Promise<Ticker[]> {
        let params = new HttpParams();

        if (limit !== null) {
            params = params.set('limit', limit);
        }

        if (offset !== null) {
            params = params.set('offset', offset);
        }

        return firstValueFrom(this.http.get<Ticker[]>(`${environment.apiUrl}/tickers/most-used`, { params }));
    }
}
