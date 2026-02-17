import { HttpClient } from '@angular/common/http';
import {inject, Injectable} from '@angular/core';
import {PriceAlert} from '@app/models';
import { OkResponse } from '@app/models/ok-response';
import { NotifyService } from '@app/services/notify-service';
import { environment } from '@environments/environment';
import { firstValueFrom } from 'rxjs';

@Injectable({ providedIn: 'root' })
export class PriceAlertService extends NotifyService {
    private readonly http = inject(HttpClient);

    public getPriceAlerts(): Promise<PriceAlert[]> {
        return firstValueFrom<PriceAlert[]>(this.http.get<PriceAlert[]>(`${environment.apiUrl}/price-alerts`));
    }

    public getPriceAlert(id: number): Promise<PriceAlert> {
        return firstValueFrom<PriceAlert>(this.http.get<PriceAlert>(`${environment.apiUrl}/price-alert/${id}`));
    }

    public createPriceAlert(priceAlert: Partial<PriceAlert>): Promise<PriceAlert> {
        return firstValueFrom<PriceAlert>(this.http.post<PriceAlert>(`${environment.apiUrl}/price-alerts`, priceAlert));
    }

    public updatePriceAlert(id: number, priceAlert: Partial<PriceAlert>): Promise<PriceAlert> {
        return firstValueFrom<PriceAlert>(this.http.put<PriceAlert>(`${environment.apiUrl}/price-alert/${id}`, priceAlert));
    }

    public deletePriceAlert(id: number): Promise<OkResponse> {
        return firstValueFrom<OkResponse>(this.http.delete<OkResponse>(`${environment.apiUrl}/price-alert/${id}`));
    }
}
