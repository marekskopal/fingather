import { HttpClient } from '@angular/common/http';
import {inject, Injectable} from '@angular/core';
import {
    DividendDataDateInterval,
} from '@app/models';
import { RangeEnum } from '@app/models/enums/range-enum';
import { NotifyService } from '@app/services/notify-service';
import { buildHttpParams } from '@app/utils/http-params-builder';
import { environment } from '@environments/environment';
import { firstValueFrom } from 'rxjs';

@Injectable({ providedIn: 'root' })
export class DividendDataService extends NotifyService {
    private readonly http = inject(HttpClient);

    public getDividendDataRange(
        portfolioId: number,
        range: RangeEnum,
    ): Promise<DividendDataDateInterval[]> {
        return firstValueFrom<DividendDataDateInterval[]>(
            this.http.get<DividendDataDateInterval[]>(
                `${environment.apiUrl}/dividend-data-range/${portfolioId}`,
                { params: buildHttpParams({ range }) },
            ),
        );
    }
}
