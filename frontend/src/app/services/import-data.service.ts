import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';

import { environment } from '@environments/environment';
import { ImportData } from '@app/models';
import {Observable} from "rxjs";
import {OkResponse} from "@app/models/ok-response";

@Injectable({ providedIn: 'root' })
export class ImportDataService {
    public constructor(
        private http: HttpClient
    ) {}

    public create(importData: ImportData): Observable<OkResponse> {
        return this.http.post<OkResponse>(`${environment.apiUrl}/import-data`, importData);
    }
}
