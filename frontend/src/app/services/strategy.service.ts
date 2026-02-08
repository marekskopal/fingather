import { HttpClient } from '@angular/common/http';
import { inject, Injectable } from '@angular/core';
import { Strategy } from '@app/models';
import { OkResponse } from '@app/models/ok-response';
import { NotifyService } from '@app/services/notify-service';
import { environment } from '@environments/environment';
import { firstValueFrom } from 'rxjs';

@Injectable({ providedIn: 'root' })
export class StrategyService extends NotifyService {
    private readonly http = inject(HttpClient);

    public getStrategies(portfolioId: number): Promise<Strategy[]> {
        return firstValueFrom<Strategy[]>(this.http.get<Strategy[]>(`${environment.apiUrl}/strategies/${portfolioId}`));
    }

    public getStrategy(id: number): Promise<Strategy> {
        return firstValueFrom<Strategy>(this.http.get<Strategy>(`${environment.apiUrl}/strategy/${id}`));
    }

    public getDefaultStrategy(portfolioId: number): Promise<Strategy> {
        return firstValueFrom<Strategy>(this.http.get<Strategy>(`${environment.apiUrl}/strategy/default/${portfolioId}`));
    }

    public createStrategy(strategy: Partial<Strategy>, portfolioId: number): Promise<Strategy> {
        return firstValueFrom<Strategy>(this.http.post<Strategy>(`${environment.apiUrl}/strategies/${portfolioId}`, strategy));
    }

    public updateStrategy(id: number, strategy: Partial<Strategy>): Promise<Strategy> {
        return firstValueFrom<Strategy>(this.http.put<Strategy>(`${environment.apiUrl}/strategy/${id}`, strategy));
    }

    public deleteStrategy(id: number): Promise<OkResponse> {
        return firstValueFrom<OkResponse>(this.http.delete<OkResponse>(`${environment.apiUrl}/strategy/${id}`));
    }
}
