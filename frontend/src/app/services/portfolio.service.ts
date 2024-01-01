import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';

import { environment } from '@environments/environment';
import { Portfolio } from '@app/models';
import {Observable} from "rxjs";

@Injectable({ providedIn: 'root' })
export class PortfolioService {
    public constructor(
        private http: HttpClient
    ) {}

    public getPortfolio(): Observable<Portfolio> {
        return this.http.get<Portfolio>(`${environment.apiUrl}/portfolio`);
    }
}
