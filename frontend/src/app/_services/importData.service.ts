import { Injectable } from '@angular/core';
import { Router } from '@angular/router';
import { HttpClient } from '@angular/common/http';

import { environment } from '@environments/environment';
import { ImportData } from '@app/_models';

@Injectable({ providedIn: 'root' })
export class ImportDataService {
    constructor(
        private router: Router,
        private http: HttpClient
    ) {}

    create(importData: ImportData) {
        return this.http.post(`${environment.apiUrl}/importdata`, importData);
    }
}
