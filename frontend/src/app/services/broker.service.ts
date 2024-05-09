import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Broker } from '@app/models';
import { NotifyService } from '@app/services/notify-service';
import { environment } from '@environments/environment';
import { firstValueFrom } from 'rxjs';

@Injectable({ providedIn: 'root' })
export class BrokerService extends NotifyService {
    public constructor(
        private http: HttpClient,
    ) {
        super();
    }

    public getBrokers(portfolioId: number): Promise<Broker[]> {
        return firstValueFrom<Broker[]>(this.http.get<Broker[]>(`${environment.apiUrl}/brokers/${portfolioId}`));
    }
}
