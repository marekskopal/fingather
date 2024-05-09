import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { TickerFundamental } from '@app/models/ticker-fundamental';
import { NotifyService } from '@app/services/notify-service';
import { environment } from '@environments/environment';
import { firstValueFrom } from 'rxjs';

@Injectable({ providedIn: 'root' })
export class TickerFundamentalService extends NotifyService {
    public constructor(
        private http: HttpClient
    ) {
        super();
    }

    public getTickerFundamental(tickerId: number): Promise<TickerFundamental> {
        return firstValueFrom<TickerFundamental>(
            this.http.get<TickerFundamental>(`${environment.apiUrl}/ticker-fundamental/${tickerId}`)
        );
    }
}
