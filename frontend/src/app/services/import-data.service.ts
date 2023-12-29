import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';

import { environment } from '@environments/environment';
import { ImportData } from '@app/models';

@Injectable({ providedIn: 'root' })
export class ImportDataService {
    constructor(
        private http: HttpClient
    ) {}

    create(importData: ImportData) {
        return this.http.post(`${environment.apiUrl}/import-data`, importData);
    }
}
