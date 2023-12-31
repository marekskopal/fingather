import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';

import { environment } from '@environments/environment';
import { Portfolio } from '@app/models';

@Injectable({ providedIn: 'root' })
export class PortfolioService {
    public constructor(
        private http: HttpClient
    ) {}

    public get() {
        return this.http.get<Portfolio>(`${environment.apiUrl}/portfolio`);
    }
}
