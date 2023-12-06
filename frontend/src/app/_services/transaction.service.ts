import { Injectable } from '@angular/core';
import { Router } from '@angular/router';
import {HttpClient, HttpParams} from '@angular/common/http';
import { map } from 'rxjs/operators';

import { environment } from '@environments/environment';
import { Transaction } from '@app/_models';

@Injectable({ providedIn: 'root' })
export class TransactionService {
    constructor(
        private router: Router,
        private http: HttpClient
    ) {}

    create(transaction: Transaction) {
        return this.http.post(`${environment.apiUrl}/transaction`, transaction);
    }

    findAll() {
        return this.http.get<Transaction[]>(`${environment.apiUrl}/transaction`);
    }

    findByAssetId(assetId: string) {
        const params = new HttpParams().set('assetId', assetId);

        return this.http.get<Transaction[]>(`${environment.apiUrl}/transaction`, {params});
    }

    getByUuid(id: string) {
        return this.http.get<Transaction>(`${environment.apiUrl}/transaction/${id}`);
    }

    update(id, params) {
        return this.http.put(`${environment.apiUrl}/transaction/${id}`, params)
            .pipe(map(x => {
                return x;
            }));
    }

    delete(id: string) {
        return this.http.delete(`${environment.apiUrl}/transaction/${id}`)
            .pipe(map(x => {
                return x;
            }));
    }
}
