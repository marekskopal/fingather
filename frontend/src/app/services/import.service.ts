import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { ImportData, ImportPrepare, ImportStart } from '@app/models';
import { OkResponse } from '@app/models/ok-response';
import { environment } from '@environments/environment';
import { Observable } from 'rxjs';

@Injectable({ providedIn: 'root' })
export class ImportService {
    public constructor(
        private http: HttpClient
    ) {}

    public createImportPrepare(importData: ImportData): Observable<ImportPrepare> {
        return this.http.post<ImportPrepare>(`${environment.apiUrl}/import/import-prepare`, importData);
    }

    public createImportStart(importStart: ImportStart): Observable<OkResponse> {
        return this.http.post<OkResponse>(`${environment.apiUrl}/import/import-start`, importStart);
    }
}
