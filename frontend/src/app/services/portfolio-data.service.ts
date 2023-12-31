import { Injectable } from '@angular/core';
import {HttpClient} from '@angular/common/http';
import { environment } from '@environments/environment';
import { PortfolioData } from '@app/models';

@Injectable({ providedIn: 'root' })
export class PortfolioDataService {
    public constructor(
        private http: HttpClient
    ) {}

    public getPortfolioData() {
        return this.http.get<PortfolioData>(`${environment.apiUrl}/portfolio-data`);
    }
}
