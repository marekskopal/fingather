import { Injectable } from '@angular/core';
import {HttpClient, HttpParams} from '@angular/common/http';
import { environment } from '@environments/environment';
import {PortfolioData, PortfolioDataRangeEnum} from '@app/models';
import {Observable} from "rxjs";
import {PortfolioDataWithBenchmarkData} from "@app/models/portfolio-data-with-benchmark-data";

@Injectable({ providedIn: 'root' })
export class PortfolioDataService {
    public constructor(
        private http: HttpClient
    ) {}

    public getPortfolioData(): Observable<PortfolioData> {
        return this.http.get<PortfolioData>(`${environment.apiUrl}/portfolio-data`);
    }

    public getPortfolioDataRange(range: PortfolioDataRangeEnum, benchmarkAssetId: number|null = null): Observable<PortfolioDataWithBenchmarkData[]> {
        let params = new HttpParams();
        params = params.set('range', range)

        if (benchmarkAssetId !== null) {
            params = params.set('benchmarkAssetId', benchmarkAssetId)
        }

        return this.http.get<PortfolioDataWithBenchmarkData[]>(`${environment.apiUrl}/portfolio-data-range`, {params});
    }
}
