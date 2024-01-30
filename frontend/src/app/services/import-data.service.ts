import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { ImportData } from '@app/models';
import {OkResponse} from '@app/models/ok-response';
import { environment } from '@environments/environment';
import {Observable} from 'rxjs';

@Injectable({ providedIn: 'root' })
export class ImportDataService {
    public constructor(
        private http: HttpClient
    ) {}

    public createImportData(importData: ImportData): Observable<OkResponse> {
        return this.http.post<OkResponse>(`${environment.apiUrl}/import-data`, importData);
    }
}
