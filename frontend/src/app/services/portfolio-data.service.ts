import { Injectable } from '@angular/core';
import {HttpClient, HttpParams} from '@angular/common/http';
import { environment } from '@environments/environment';
import {PortfolioData, PortfolioDataRangeEnum} from '@app/models';
import {Observable} from "rxjs";

@Injectable({ providedIn: 'root' })
export class PortfolioDataService {
    public constructor(
        private http: HttpClient
    ) {}

    public getPortfolioData(): Observable<PortfolioData> {
        return this.http.get<PortfolioData>(`${environment.apiUrl}/portfolio-data`);
    }

    public getPortfolioDataRange(range: PortfolioDataRangeEnum): Observable<PortfolioData[]> {
        let params = new HttpParams();
        params = params.set('range', range)

        return this.http.get<PortfolioData[]>(`${environment.apiUrl}/portfolio-data-range`, {params});
    }
}
