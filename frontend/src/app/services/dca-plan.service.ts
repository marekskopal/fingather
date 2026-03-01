import { HttpClient, HttpParams } from '@angular/common/http';
import { inject, Injectable } from '@angular/core';
import { DcaPlan, DcaPlanProjection } from '@app/models';
import { OkResponse } from '@app/models/ok-response';
import { NotifyService } from '@app/services/notify-service';
import { environment } from '@environments/environment';
import { firstValueFrom } from 'rxjs';

@Injectable({ providedIn: 'root' })
export class DcaPlanService extends NotifyService {
    private readonly http = inject(HttpClient);

    public getDcaPlans(portfolioId: number): Promise<DcaPlan[]> {
        return firstValueFrom<DcaPlan[]>(this.http.get<DcaPlan[]>(`${environment.apiUrl}/dca-plans/${portfolioId}`));
    }

    public getDcaPlan(id: number): Promise<DcaPlan> {
        return firstValueFrom<DcaPlan>(this.http.get<DcaPlan>(`${environment.apiUrl}/dca-plan/${id}`));
    }

    public createDcaPlan(portfolioId: number, dcaPlan: Partial<DcaPlan>): Promise<DcaPlan> {
        return firstValueFrom<DcaPlan>(this.http.post<DcaPlan>(`${environment.apiUrl}/dca-plans/${portfolioId}`, dcaPlan));
    }

    public updateDcaPlan(id: number, dcaPlan: Partial<DcaPlan>): Promise<DcaPlan> {
        return firstValueFrom<DcaPlan>(this.http.put<DcaPlan>(`${environment.apiUrl}/dca-plan/${id}`, dcaPlan));
    }

    public deleteDcaPlan(id: number): Promise<OkResponse> {
        return firstValueFrom<OkResponse>(this.http.delete<OkResponse>(`${environment.apiUrl}/dca-plan/${id}`));
    }

    public getProjection(id: number, horizonYears?: number, withCurrentValue: boolean = true): Promise<DcaPlanProjection> {
        let params = new HttpParams();
        if (horizonYears !== undefined) {
            params = params.set('horizonYears', horizonYears.toString());
        }
        params = params.set('withCurrentValue', withCurrentValue.toString());
        return firstValueFrom<DcaPlanProjection>(
            this.http.get<DcaPlanProjection>(`${environment.apiUrl}/dca-plan/${id}/projection`, { params }),
        );
    }
}
