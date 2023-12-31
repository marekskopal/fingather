import {Injectable} from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { map } from 'rxjs/operators';

import { environment } from '@environments/environment';
import { Broker } from '@app/models';
import {NotifyService} from "@app/services/notify-service";

@Injectable({ providedIn: 'root' })
export class BrokerService extends NotifyService {
    public constructor(
        private http: HttpClient,
    ) {
        super();
    }

    public create(broker: Broker) {
        return this.http.post(`${environment.apiUrl}/broker`, broker);
    }

    public findAll() {
        return this.http.get<Broker[]>(`${environment.apiUrl}/broker`);
    }

    public getByUuid(id: number) {
        return this.http.get<Broker>(`${environment.apiUrl}/broker/${id}`);
    }

    public update(id: number, broker: Broker) {
        return this.http.put(`${environment.apiUrl}/broker/${id}`, broker)
            .pipe(map(x => {
                return x;
            }));
    }

    public delete(id: number) {
        return this.http.delete(`${environment.apiUrl}/broker/${id}`)
            .pipe(map(x => {
                return x;
            }));
    }
}
