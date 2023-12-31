import { Injectable } from '@angular/core';
import {HttpClient, HttpParams} from '@angular/common/http';
import { map } from 'rxjs/operators';

import { environment } from '@environments/environment';
import { Transaction } from '@app/models';
import {Observable} from "rxjs";
import {OkResponse} from "@app/models/ok-response";

@Injectable({ providedIn: 'root' })
export class TransactionService {
    public constructor(
        private http: HttpClient
    ) {}

    public create(transaction: Transaction): Observable<Transaction> {
        return this.http.post<Transaction>(`${environment.apiUrl}/transaction`, transaction);
    }

    public findAll(): Observable<Transaction[]> {
        return this.http.get<Transaction[]>(`${environment.apiUrl}/transaction`);
    }

    public findByAssetId(assetId: number): Observable<Transaction[]> {
        const params = new HttpParams().set('assetId', assetId);

        return this.http.get<Transaction[]>(`${environment.apiUrl}/transaction`, {params});
    }

    public getByUuid(id: number): Observable<Transaction> {
        return this.http.get<Transaction>(`${environment.apiUrl}/transaction/${id}`);
    }

    public update(id: number, transaction: Transaction): Observable<Transaction> {
        return this.http.put<Transaction>(`${environment.apiUrl}/transaction/${id}`, transaction)
            .pipe(map(x => {
                return x;
            }));
    }

    public delete(id: number): Observable<OkResponse> {
        return this.http.delete<OkResponse>(`${environment.apiUrl}/transaction/${id}`)
            .pipe(map(x => {
                return x;
            }));
    }
}
