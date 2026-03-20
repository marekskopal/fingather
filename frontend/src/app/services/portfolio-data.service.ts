import { HttpClient } from '@angular/common/http';
import {inject, Injectable} from '@angular/core';
import { PortfolioData } from '@app/models';
import { RangeEnum } from '@app/models/enums/range-enum';
import { PortfolioDataWithBenchmarkData } from '@app/models/portfolio-data-with-benchmark-data';
import { buildHttpParams } from '@app/utils/http-params-builder';
import { environment } from '@environments/environment';
import { firstValueFrom } from 'rxjs';

@Injectable({ providedIn: 'root' })
export class PortfolioDataService {
    private readonly http = inject(HttpClient);

    public getPortfolioData(portfolioId: number): Promise<PortfolioData> {
        return firstValueFrom(this.http.get<PortfolioData>(`${environment.apiUrl}/portfolio-data/${portfolioId}`));
    }

    public getPortfolioDataRange(
        portfolioId: number,
        range: RangeEnum,
        benchmarkAssetId: number | null = null,
        benchmarkTickerId: number | null = null,
        customRangeFrom: string | null = null,
        customRangeTo: string | null = null,
    ): Promise<PortfolioDataWithBenchmarkData[]> {
        return firstValueFrom(this.http.get<PortfolioDataWithBenchmarkData[]>(
            `${environment.apiUrl}/portfolio-data-range/${portfolioId}`,
            { params: buildHttpParams({ range, benchmarkAssetId, benchmarkTickerId, customRangeFrom, customRangeTo }) },
        ));
    }
}
