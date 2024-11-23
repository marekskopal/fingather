import { HttpClient } from '@angular/common/http';
import {inject, Injectable} from '@angular/core';
import { ImportPrepare, ImportPrepareData, ImportStart} from '@app/models';
import { OkResponse } from '@app/models/ok-response';
import { environment } from '@environments/environment';
import { firstValueFrom } from 'rxjs';

@Injectable({ providedIn: 'root' })
export class ImportService {
    private readonly http = inject(HttpClient);

    public createImportPrepare(importPrepareData: ImportPrepareData, portfolioId: number): Promise<ImportPrepare> {
        return firstValueFrom<ImportPrepare>(
            this.http.post<ImportPrepare>(
                `${environment.apiUrl}/import/import-prepare/${portfolioId}`,
                importPrepareData,
            ),
        );
    }

    public createImportStart(importStart: ImportStart): Promise<OkResponse> {
        return firstValueFrom<OkResponse>(
            this.http.post<OkResponse>(`${environment.apiUrl}/import/import-start`, importStart),
        );
    }

    public deleteImportFile(importFileId: number): Promise<OkResponse> {
        return firstValueFrom<OkResponse>(
            this.http.delete<OkResponse>(`${environment.apiUrl}/import/import-file/${importFileId}`),
        );
    }
}
