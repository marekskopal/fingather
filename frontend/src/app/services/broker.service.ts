import {EventEmitter, Injectable} from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { map } from 'rxjs/operators';

import { environment } from '@environments/environment';
import { Broker } from '@app/models';

@Injectable({ providedIn: 'root' })
export class BrokerService {
    public eventEmitter: EventEmitter<null> = new EventEmitter();

    constructor(
        private http: HttpClient,
    ) {}

    create(broker: Broker) {
        return this.http.post(`${environment.apiUrl}/broker`, broker);
    }

    findAll() {
        return this.http.get<Broker[]>(`${environment.apiUrl}/broker`);
    }

    getByUuid(id: number) {
        return this.http.get<Broker>(`${environment.apiUrl}/broker/${id}`);
    }

    update(id: number, params) {
        return this.http.put(`${environment.apiUrl}/broker/${id}`, params)
            .pipe(map(x => {
                return x;
            }));
    }

    delete(id: number) {
        return this.http.delete(`${environment.apiUrl}/broker/${id}`)
            .pipe(map(x => {
                return x;
            }));
    }

    notify() {
        this.eventEmitter.emit();
    }
}
