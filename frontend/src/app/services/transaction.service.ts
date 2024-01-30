import {HttpClient, HttpParams} from '@angular/common/http';
import { Injectable } from '@angular/core';
import {Transaction, TransactionActionType} from '@app/models';
import {OkResponse} from '@app/models/ok-response';
import {TransactionList} from '@app/models/TransactionList';
import {NotifyService} from '@app/services/notify-service';
import { environment } from '@environments/environment';
import {Observable} from 'rxjs';
import { map } from 'rxjs/operators';

@Injectable({ providedIn: 'root' })
export class TransactionService extends NotifyService {
    public constructor(
        private http: HttpClient
    ) {
        super();
    }

    public createTransaction(transaction: Transaction, portfolioId: number): Observable<Transaction> {
        return this.http.post<Transaction>(`${environment.apiUrl}/transactions/${portfolioId}`, transaction);
    }

    public getTransactions(
        portfolioId: number,
        assetId: number|null = null,
        actionTypes: TransactionActionType[]|null = null,
        limit: number|null = null,
        offset: number|null = null,
    ): Observable<TransactionList> {
        let params = new HttpParams();

        if (assetId !== null) {
            params = params.set('assetId', assetId);
        }

        if (actionTypes !== null) {
            params = params.set('actionTypes', actionTypes.join('|'));
        }

        if (limit !== null) {
            params = params.set('limit', limit);
        }

        if (offset !== null) {
            params = params.set('offset', offset);
        }

        return this.http.get<TransactionList>(`${environment.apiUrl}/transactions/${portfolioId}`, {params});
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
