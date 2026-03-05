import {HttpClient} from '@angular/common/http';
import {inject, Injectable} from '@angular/core';
import {ImportMappingDetails} from '@app/models/import-mapping-details';
import {OkResponse} from '@app/models/ok-response';
import {NotifyService} from '@app/services/notify-service';
import {environment} from '@environments/environment';
import {firstValueFrom} from 'rxjs';

@Injectable({providedIn: 'root'})
export class ImportMappingService extends NotifyService {
    private readonly http = inject(HttpClient);

    public getImportMappings(portfolioId: number): Promise<ImportMappingDetails[]> {
        return firstValueFrom<ImportMappingDetails[]>(
            this.http.get<ImportMappingDetails[]>(`${environment.apiUrl}/import-mappings/${portfolioId}`),
        );
    }

    public getImportMapping(id: number): Promise<ImportMappingDetails> {
        return firstValueFrom<ImportMappingDetails>(
            this.http.get<ImportMappingDetails>(`${environment.apiUrl}/import-mapping/${id}`),
        );
    }

    public updateImportMapping(id: number, tickerId: number): Promise<ImportMappingDetails> {
        return firstValueFrom<ImportMappingDetails>(
            this.http.put<ImportMappingDetails>(`${environment.apiUrl}/import-mapping/${id}`, {tickerId}),
        );
    }

    public deleteImportMapping(id: number): Promise<OkResponse> {
        return firstValueFrom<OkResponse>(
            this.http.delete<OkResponse>(`${environment.apiUrl}/import-mapping/${id}`),
        );
    }
}
