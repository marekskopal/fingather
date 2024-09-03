import { HttpClient } from '@angular/common/http';
import {inject, Injectable} from '@angular/core';
import {ApiKey} from '@app/models';
import { OkResponse } from '@app/models/ok-response';
import { NotifyService } from '@app/services/notify-service';
import { environment } from '@environments/environment';
import { firstValueFrom } from 'rxjs';

@Injectable({ providedIn: 'root' })
export class ApiKeyService extends NotifyService {
    private readonly http = inject(HttpClient);

    public createApiKey(apiKey: ApiKey, portfolioId: number): Promise<ApiKey> {
        return firstValueFrom<ApiKey>(this.http.post<ApiKey>(`${environment.apiUrl}/api-keys/${portfolioId}`, apiKey));
    }

    public getApiKeys(portfolioId: number): Promise<ApiKey[]> {
        return firstValueFrom<ApiKey[]>(this.http.get<ApiKey[]>(`${environment.apiUrl}/api-keys/${portfolioId}`));
    }

    public getApiKey(id: number): Promise<ApiKey> {
        return firstValueFrom<ApiKey>(this.http.get<ApiKey>(`${environment.apiUrl}/api-key/${id}`));
    }

    public updateApiKey(id: number, group: ApiKey): Promise<ApiKey> {
        return firstValueFrom<ApiKey>(this.http.put<ApiKey>(`${environment.apiUrl}/api-key/${id}`, group));
    }

    public deleteApiKey(id: number): Promise<OkResponse> {
        return firstValueFrom<OkResponse>(this.http.delete<OkResponse>(`${environment.apiUrl}/api-key/${id}`));
    }
}
