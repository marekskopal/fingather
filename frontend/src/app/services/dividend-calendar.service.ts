import { HttpClient } from '@angular/common/http';
import {inject, Injectable} from '@angular/core';
import { DividendCalendarItem } from '@app/models';
import { NotifyService } from '@app/services/notify-service';
import { environment } from '@environments/environment';
import { firstValueFrom } from 'rxjs';

@Injectable({ providedIn: 'root' })
export class DividendCalendarService extends NotifyService {
    private readonly http = inject(HttpClient);

    public getDividendCalendar(portfolioId: number): Promise<DividendCalendarItem[]> {
        return firstValueFrom<DividendCalendarItem[]>(
            this.http.get<DividendCalendarItem[]>(
                `${environment.apiUrl}/dividend-calendar/${portfolioId}`,
            ),
        );
    }
}
