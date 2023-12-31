import {Injectable} from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { map } from 'rxjs/operators';

import { environment } from '@environments/environment';
import { Broker } from '@app/models';
import {NotifyService} from "@app/services/notify-service";
import {Observable} from "rxjs";
import {OkResponse} from "@app/models/ok-response";

@Injectable({ providedIn: 'root' })
export class BrokerService extends NotifyService {
    public constructor(
        private http: HttpClient,
    ) {
        super();
    }

    public create(broker: Broker): Observable<Broker> {
        return this.http.post<Broker>(`${environment.apiUrl}/broker`, broker);
    }

    public findAll(): Observable<Broker[]> {
        return this.http.get<Broker[]>(`${environment.apiUrl}/broker`);
    }

    public getByUuid(id: number): Observable<Broker> {
        return this.http.get<Broker>(`${environment.apiUrl}/broker/${id}`);
    }

    public update(id: number, broker: Broker): Observable<Broker> {
        return this.http.put<Broker>(`${environment.apiUrl}/broker/${id}`, broker)
            .pipe(map(x => {
                return x;
            }));
    }

    public delete(id: number): Observable<OkResponse> {
        return this.http.delete<OkResponse>(`${environment.apiUrl}/broker/${id}`)
            .pipe(map(x => {
                return x;
            }));
    }
}
