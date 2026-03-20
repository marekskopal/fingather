import { HttpClient } from '@angular/common/http';
import {inject, Injectable} from '@angular/core';
import { Ticker } from '@app/models';
import { buildHttpParams } from '@app/utils/http-params-builder';
import { environment } from '@environments/environment';
import {firstValueFrom} from 'rxjs';

@Injectable({ providedIn: 'root' })
export class TickerService {
    private readonly http = inject(HttpClient);

    public getTicker(id: number): Promise<Ticker> {
        return firstValueFrom(this.http.get<Ticker>(`${environment.apiUrl}/ticker/${id}`));
    }

    public getTickers(
        search: string | null = null,
        limit: number | null = null,
        offset: number | null = null,
    ): Promise<Ticker[]> {
        return firstValueFrom(this.http.get<Ticker[]>(`${environment.apiUrl}/tickers`, {
            params: buildHttpParams({ search, limit, offset }),
        }));
    }

    public getTickersMostUsed(
        limit: number | null = null,
        offset: number | null = null,
    ): Promise<Ticker[]> {
        return firstValueFrom(this.http.get<Ticker[]>(`${environment.apiUrl}/tickers/most-used`, {
            params: buildHttpParams({ limit, offset }),
        }));
    }
}
