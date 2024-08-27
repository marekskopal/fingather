import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import {ImportData, ImportDataFile, ImportPrepare, ImportStart} from '@app/models';
import { OkResponse } from '@app/models/ok-response';
import { environment } from '@environments/environment';
import { firstValueFrom } from 'rxjs';

@Injectable({ providedIn: 'root' })
export class ImportService {
    public constructor(
        private http: HttpClient
    ) {}

    public createImportPrepare(importData: ImportData, portfolioId: number): Promise<ImportPrepare> {
        return firstValueFrom<ImportPrepare>(
            this.http.post<ImportPrepare>(`${environment.apiUrl}/import/import-prepare/${portfolioId}`, importData)
        );
    }

    public createImportStart(importStart: ImportStart): Promise<OkResponse> {
        return firstValueFrom<OkResponse>(
            this.http.post<OkResponse>(`${environment.apiUrl}/import/import-start`, importStart)
        );
    }
}
