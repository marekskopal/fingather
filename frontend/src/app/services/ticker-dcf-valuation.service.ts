import { HttpClient, HttpParams } from '@angular/common/http';
import {inject, Injectable} from '@angular/core';
import { TickerDcfValuation } from '@app/models/ticker-dcf-valuation';
import { NotifyService } from '@app/services/notify-service';
import { environment } from '@environments/environment';
import { firstValueFrom } from 'rxjs';

export interface TickerDcfValuationOverrides {
    wacc?: number;
    terminalGrowthRate?: number;
    projectionYears?: number;
    growthRate?: number;
    fcfMargin?: number;
}

@Injectable({ providedIn: 'root' })
export class TickerDcfValuationService extends NotifyService {
    private readonly http = inject(HttpClient);

    public getTickerDcfValuation(
        tickerId: number,
        overrides: TickerDcfValuationOverrides = {},
    ): Promise<TickerDcfValuation> {
        let params = new HttpParams();
        for (const [key, value] of Object.entries(overrides)) {
            if (value !== undefined && value !== null) {
                params = params.set(key, String(value));
            }
        }

        return firstValueFrom<TickerDcfValuation>(
            this.http.get<TickerDcfValuation>(
                `${environment.apiUrl}/ticker-dcf-valuation/${tickerId}`,
                { params },
            ),
        );
    }
}
