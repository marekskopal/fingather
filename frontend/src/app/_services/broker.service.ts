import {EventEmitter, Injectable} from '@angular/core';
import { Router } from '@angular/router';
import { HttpClient } from '@angular/common/http';
import { BehaviorSubject, Observable } from 'rxjs';
import { map } from 'rxjs/operators';

import { environment } from '@environments/environment';
import { Broker } from '@app/_models';

@Injectable({ providedIn: 'root' })
export class BrokerService {
    public eventEmitter: EventEmitter<null> = new EventEmitter();

    constructor(
        private router: Router,
        private http: HttpClient,
    ) {}

    create(broker: Broker) {
        return this.http.post(`${environment.apiUrl}/broker`, broker);
    }

    findAll() {
        return this.http.get<Broker[]>(`${environment.apiUrl}/broker`);
    }

    getByUuid(id: string) {
        return this.http.get<Broker>(`${environment.apiUrl}/broker/${id}`);
    }

    update(id, params) {
        return this.http.put(`${environment.apiUrl}/broker/${id}`, params)
            .pipe(map(x => {
                return x;
            }));
    }

    delete(id: string) {
        return this.http.delete(`${environment.apiUrl}/broker/${id}`)
            .pipe(map(x => {
                return x;
            }));
    }

    notify() {
        this.eventEmitter.emit();
    }
}
