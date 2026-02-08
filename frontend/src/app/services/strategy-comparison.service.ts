import { HttpClient } from '@angular/common/http';
import { inject, Injectable } from '@angular/core';
import { StrategyWithComparison } from '@app/models';
import { environment } from '@environments/environment';
import { firstValueFrom } from 'rxjs';

@Injectable({ providedIn: 'root' })
export class StrategyComparisonService {
    private readonly http = inject(HttpClient);

    public getStrategyWithComparison(strategyId: number): Promise<StrategyWithComparison> {
        return firstValueFrom<StrategyWithComparison>(
            this.http.get<StrategyWithComparison>(`${environment.apiUrl}/strategy-with-comparison/${strategyId}`),
        );
    }
}
