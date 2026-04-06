import { HttpClient } from '@angular/common/http';
import {inject, Injectable} from '@angular/core';
import {McpApiKey} from '@app/models';
import { OkResponse } from '@app/models/ok-response';
import { NotifyService } from '@app/services/notify-service';
import { environment } from '@environments/environment';
import { firstValueFrom } from 'rxjs';

@Injectable({ providedIn: 'root' })
export class McpApiKeyService extends NotifyService {
    private readonly http = inject(HttpClient);

    public getMcpApiKeys(): Promise<McpApiKey[]> {
        return firstValueFrom<McpApiKey[]>(this.http.get<McpApiKey[]>(`${environment.apiUrl}/mcp-api-keys`));
    }

    public async getFullApiKey(id: number): Promise<string> {
        const response = await firstValueFrom<{ apiKey: string }>(this.http.get<{ apiKey: string }>(`${environment.apiUrl}/mcp-api-key/${id}`));
        return response.apiKey;
    }

    public createMcpApiKey(name: string): Promise<McpApiKey> {
        return firstValueFrom<McpApiKey>(this.http.post<McpApiKey>(`${environment.apiUrl}/mcp-api-keys`, { name }));
    }

    public deleteMcpApiKey(id: number): Promise<OkResponse> {
        return firstValueFrom<OkResponse>(this.http.delete<OkResponse>(`${environment.apiUrl}/mcp-api-key/${id}`));
    }
}
