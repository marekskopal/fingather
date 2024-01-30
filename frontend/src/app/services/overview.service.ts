import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { YearCalculatedData } from '@app/models/year-calculated-data';
import { environment } from '@environments/environment';
import { Observable } from 'rxjs';

@Injectable({ providedIn: 'root' })
export class OverviewService {
    public constructor(
        private http: HttpClient
    ) {}

    public getYearCalculatedData(portfolioId: number): Observable<YearCalculatedData[]> {
        return this.http.get<YearCalculatedData[]>(`${environment.apiUrl}/overview/year-overview/${portfolioId}`);
    }
}
