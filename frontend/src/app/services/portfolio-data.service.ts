import { Injectable } from '@angular/core';
import {HttpClient} from '@angular/common/http';
import { environment } from '@environments/environment';
import { PortfolioData } from '@app/models';
import {Observable} from "rxjs";

@Injectable({ providedIn: 'root' })
export class PortfolioDataService {
    public constructor(
        private http: HttpClient
    ) {}

    public getPortfolioData(): Observable<PortfolioData> {
        return this.http.get<PortfolioData>(`${environment.apiUrl}/portfolio-data`);
    }
}
