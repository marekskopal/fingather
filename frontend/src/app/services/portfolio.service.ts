import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { map } from 'rxjs/operators';

import { environment } from '@environments/environment';
import { Portfolio } from '@app/models';

@Injectable({ providedIn: 'root' })
export class PortfolioService {
    constructor(
        private http: HttpClient
    ) {}

    get() {
        return this.http.get<Portfolio>(`${environment.apiUrl}/portfolio`);
    }
}
