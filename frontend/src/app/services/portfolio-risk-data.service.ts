import { HttpClient, HttpParams } from '@angular/common/http';
import { inject, Injectable } from '@angular/core';
import { PortfolioRiskData } from '@app/models';
import { RangeEnum } from '@app/models/enums/range-enum';
import { SamplingFrequencyEnum } from '@app/models/enums/sampling-frequency-enum';
import { environment } from '@environments/environment';
import { firstValueFrom } from 'rxjs';

@Injectable({ providedIn: 'root' })
export class PortfolioRiskDataService {
    private readonly http = inject(HttpClient);

    public getPortfolioRiskData(
        portfolioId: number,
        range: RangeEnum,
        samplingFrequency: SamplingFrequencyEnum,
        benchmarkTickerId: number | null = null,
        customRangeFrom: string | null = null,
        customRangeTo: string | null = null,
    ): Promise<PortfolioRiskData> {
        let params = new HttpParams();
        params = params.set('range', range);
        params = params.set('samplingFrequency', samplingFrequency);

        if (benchmarkTickerId !== null) {
            params = params.set('benchmarkTickerId', benchmarkTickerId);
        }

        if (customRangeFrom !== null) {
            params = params.set('customRangeFrom', customRangeFrom);
        }

        if (customRangeTo !== null) {
            params = params.set('customRangeTo', customRangeTo);
        }

        return firstValueFrom(
            this.http.get<PortfolioRiskData>(`${environment.apiUrl}/portfolio-risk-data/${portfolioId}`, { params }),
        );
    }
}
