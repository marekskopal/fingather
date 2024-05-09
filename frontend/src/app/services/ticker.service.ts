import { HttpClient, HttpParams } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Ticker } from '@app/models';
import { environment } from '@environments/environment';
import { Observable } from 'rxjs';

@Injectable({ providedIn: 'root' })
export class TickerService {
    public constructor(
        private http: HttpClient
    ) {}

    public getTickers(
        search: string | null = null,
        limit: number | null = null,
        offset: number | null = null
    ): Observable<Ticker[]> {
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

        return this.http.get<Ticker[]>(`${environment.apiUrl}/ticker`, { params });
    }
}
