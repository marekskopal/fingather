import { HttpClient } from '@angular/common/http';
import {inject, Injectable} from '@angular/core';
import { YearCalculatedData } from '@app/models/year-calculated-data';
import { environment } from '@environments/environment';
import { firstValueFrom } from 'rxjs';

@Injectable({ providedIn: 'root' })
export class OverviewService {
    private readonly http = inject(HttpClient);

    public getYearCalculatedData(portfolioId: number): Promise<YearCalculatedData[]> {
        return firstValueFrom<YearCalculatedData[]>(
            this.http.get<YearCalculatedData[]>(`${environment.apiUrl}/overview/year-overview/${portfolioId}`),
        );
    }
}
