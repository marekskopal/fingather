import { Injectable } from '@angular/core';
import {HttpClient, HttpParams} from '@angular/common/http';
import { map } from 'rxjs/operators';

import { environment } from '@environments/environment';
import {Transaction, TransactionActionType} from '@app/models';
import {Observable} from "rxjs";
import {OkResponse} from "@app/models/ok-response";
import {NotifyService} from "@app/services/notify-service";

@Injectable({ providedIn: 'root' })
export class TransactionService extends NotifyService {
    public constructor(
        private http: HttpClient
    ) {
        super();
    }

    public createTransaction(transaction: Transaction): Observable<Transaction> {
        return this.http.post<Transaction>(`${environment.apiUrl}/transaction`, transaction);
    }

    public getTransactions(
        assetId: number,
        actionTypes: TransactionActionType[]|null,
        limit: number|null = null,
        offset: number|null = null,
    ): Observable<Transaction[]> {
        const params = new HttpParams().set('assetId', assetId);

        if (actionTypes !== null) {
            params.set('actionTypes', actionTypes.join('|'));
        }

        if (limit !== null) {
            params.set('limit', limit);
        }

        if (offset !== null) {
            params.set('offset', offset);
        }

        return this.http.get<Transaction[]>(`${environment.apiUrl}/transaction`, {params});
    }

    public getTransaction(id: number): Observable<Transaction> {
        return this.http.get<Transaction>(`${environment.apiUrl}/transaction/${id}`);
    }

    public updateTransaction(id: number, transaction: Transaction): Observable<Transaction> {
        return this.http.put<Transaction>(`${environment.apiUrl}/transaction/${id}`, transaction)
            .pipe(map(x => {
                return x;
            }));
    }

    public deleteTransaction(id: number): Observable<OkResponse> {
        return this.http.delete<OkResponse>(`${environment.apiUrl}/transaction/${id}`)
            .pipe(map(x => {
                return x;
            }));
    }
}
