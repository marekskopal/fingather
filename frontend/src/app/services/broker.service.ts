import { HttpClient } from '@angular/common/http';
import {Injectable} from '@angular/core';
import { Broker } from '@app/models';
import {OkResponse} from '@app/models/ok-response';
import {NotifyService} from '@app/services/notify-service';
import { environment } from '@environments/environment';
import {Observable} from 'rxjs';
import { map } from 'rxjs/operators';

@Injectable({ providedIn: 'root' })
export class BrokerService extends NotifyService {
    public constructor(
        private http: HttpClient,
    ) {
        super();
    }

    public createBroker(broker: Broker, portfolioId: number): Observable<Broker> {
        return this.http.post<Broker>(`${environment.apiUrl}/brokers/${portfolioId}`, broker);
    }

    public getBrokers(portfolioId: number): Observable<Broker[]> {
        return this.http.get<Broker[]>(`${environment.apiUrl}/brokers/${portfolioId}`);
    }

    public getBroker(id: number): Observable<Broker> {
        return this.http.get<Broker>(`${environment.apiUrl}/broker/${id}`);
    }

    public updateBroker(id: number, broker: Broker): Observable<Broker> {
        return this.http.put<Broker>(`${environment.apiUrl}/broker/${id}`, broker)
            .pipe(map(x => {
                return x;
            }));
    }

    public deleteBroker(id: number): Observable<OkResponse> {
        return this.http.delete<OkResponse>(`${environment.apiUrl}/broker/${id}`)
            .pipe(map(x => {
                return x;
            }));
    }
}
