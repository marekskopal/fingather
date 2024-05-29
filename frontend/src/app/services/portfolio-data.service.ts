import { HttpClient, HttpParams } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { PortfolioData } from '@app/models';
import { RangeEnum } from '@app/models/enums/range-enum';
import { PortfolioDataWithBenchmarkData } from '@app/models/portfolio-data-with-benchmark-data';
import { environment } from '@environments/environment';
import { firstValueFrom } from 'rxjs';

@Injectable({ providedIn: 'root' })
export class PortfolioDataService {
    public constructor(
        private http: HttpClient
    ) {}

    public getPortfolioData(portfolioId: number): Promise<PortfolioData> {
        return firstValueFrom(this.http.get<PortfolioData>(`${environment.apiUrl}/portfolio-data/${portfolioId}`));
    }

    public getPortfolioDataRange(
        portfolioId: number,
        range: RangeEnum,
        benchmarkAssetId: number | null = null
    ): Promise<PortfolioDataWithBenchmarkData[]> {
        let params = new HttpParams();
        params = params.set('range', range);

        if (benchmarkAssetId !== null) {
            params = params.set('benchmarkAssetId', benchmarkAssetId);
        }

        return firstValueFrom(this.http.get<PortfolioDataWithBenchmarkData[]>(
            `${environment.apiUrl}/portfolio-data-range/${portfolioId}`,
            { params }
        ));
    }
}
