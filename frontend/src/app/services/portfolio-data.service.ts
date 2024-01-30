import {HttpClient, HttpParams} from '@angular/common/http';
import { Injectable } from '@angular/core';
import {PortfolioData, PortfolioDataRangeEnum} from '@app/models';
import {PortfolioDataWithBenchmarkData} from '@app/models/portfolio-data-with-benchmark-data';
import { environment } from '@environments/environment';
import {Observable} from 'rxjs';

@Injectable({ providedIn: 'root' })
export class PortfolioDataService {
    public constructor(
        private http: HttpClient
    ) {}

    public getPortfolioData(portfolioId: number): Observable<PortfolioData> {
        return this.http.get<PortfolioData>(`${environment.apiUrl}/portfolio-data/${portfolioId}`);
    }

    public getPortfolioDataRange(
        portfolioId: number,
        range: PortfolioDataRangeEnum,
        benchmarkAssetId: number|null = null
    ): Observable<PortfolioDataWithBenchmarkData[]> {
        let params = new HttpParams();
        params = params.set('range', range)

        if (benchmarkAssetId !== null) {
            params = params.set('benchmarkAssetId', benchmarkAssetId)
        }

        return this.http.get<PortfolioDataWithBenchmarkData[]>(
            `${environment.apiUrl}/portfolio-data-range/${portfolioId}`,
            {params}
        );
    }
}
