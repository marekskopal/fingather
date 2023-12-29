import { Injectable } from '@angular/core';
import {HttpClient, HttpParams} from '@angular/common/http';
import { map } from 'rxjs/operators';

import { environment } from '@environments/environment';
import { Transaction } from '@app/models';

@Injectable({ providedIn: 'root' })
export class TransactionService {
    constructor(
        private http: HttpClient
    ) {}

    create(transaction: Transaction) {
        return this.http.post(`${environment.apiUrl}/transaction`, transaction);
    }

    findAll() {
        return this.http.get<Transaction[]>(`${environment.apiUrl}/transaction`);
    }

    findByAssetId(assetId: number) {
        const params = new HttpParams().set('assetId', assetId);

        return this.http.get<Transaction[]>(`${environment.apiUrl}/transaction`, {params});
    }

    getByUuid(id: number) {
        return this.http.get<Transaction>(`${environment.apiUrl}/transaction/${id}`);
    }

    update(id: number, transaction: Transaction) {
        return this.http.put(`${environment.apiUrl}/transaction/${id}`, transaction)
            .pipe(map(x => {
                return x;
            }));
    }

    delete(id: number) {
        return this.http.delete(`${environment.apiUrl}/transaction/${id}`)
            .pipe(map(x => {
                return x;
            }));
    }
}
