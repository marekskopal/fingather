import { HttpClient, HttpParams } from '@angular/common/http';
import { Injectable } from '@angular/core';
import {
    DividendDataDateInterval,
    PortfolioDataRangeEnum
} from '@app/models';
import { NotifyService } from '@app/services/notify-service';
import { environment } from '@environments/environment';
import { Observable } from 'rxjs';

@Injectable({ providedIn: 'root' })
export class DividendDataService extends NotifyService {
    public constructor(
        private http: HttpClient
    ) {
        super();
    }

    public getDividendDataRange(
        portfolioId: number,
        range: PortfolioDataRangeEnum,
    ): Observable<DividendDataDateInterval[]> {
        let params = new HttpParams();
        params = params.set('range', range);

        return this.http.get<DividendDataDateInterval[]>(
            `${environment.apiUrl}/dividend-data-range/${portfolioId}`,
            { params }
        );
    }
}
