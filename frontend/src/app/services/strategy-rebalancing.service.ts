import { HttpClient } from '@angular/common/http';
import { inject, Injectable } from '@angular/core';
import { StrategyRebalancing, StrategyRebalancingRequest } from '@app/models';
import { environment } from '@environments/environment';
import { firstValueFrom } from 'rxjs';

@Injectable({ providedIn: 'root' })
export class StrategyRebalancingService {
    private readonly http = inject(HttpClient);

    public calculate(strategyId: number, request: StrategyRebalancingRequest): Promise<StrategyRebalancing> {
        return firstValueFrom(
            this.http.post<StrategyRebalancing>(
                `${environment.apiUrl}/strategy-rebalancing/${strategyId}`,
                request,
            ),
        );
    }
}
