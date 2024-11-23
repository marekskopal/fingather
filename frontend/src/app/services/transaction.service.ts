import { HttpClient, HttpParams } from '@angular/common/http';
import {inject, Injectable} from '@angular/core';
import { Transaction, TransactionActionType } from '@app/models';
import { OkResponse } from '@app/models/ok-response';
import { TransactionList } from '@app/models/transaction-list';
import { NotifyService } from '@app/services/notify-service';
import { environment } from '@environments/environment';
import { firstValueFrom } from 'rxjs';

@Injectable({ providedIn: 'root' })
export class TransactionService extends NotifyService {
    private readonly http = inject(HttpClient);

    public createTransaction(transaction: Transaction, portfolioId: number): Promise<Transaction> {
        return firstValueFrom<Transaction>(
            this.http.post<Transaction>(`${environment.apiUrl}/transactions/${portfolioId}`, transaction),
        );
    }

    public getTransactions(
        portfolioId: number,
        assetId: number | null = null,
        actionTypes: TransactionActionType[] | null = null,
        search: string | null = null,
        created: string | null = null,
        limit: number | null = null,
        offset: number | null = null,
    ): Promise<TransactionList> {
        let params = new HttpParams();

        if (assetId !== null) {
            params = params.set('assetId', assetId);
        }

        if (actionTypes !== null) {
            params = params.set('actionTypes', actionTypes.join('|'));
        }

        if (search !== null) {
            params = params.set('search', search);
        }

        if (created !== null) {
            params = params.set('created', created);
        }

        if (limit !== null) {
            params = params.set('limit', limit);
        }

        if (offset !== null) {
            params = params.set('offset', offset);
        }

        return firstValueFrom<TransactionList>(
            this.http.get<TransactionList>(`${environment.apiUrl}/transactions/${portfolioId}`, { params }),
        );
    }

    public getTransaction(id: number): Promise<Transaction> {
        return firstValueFrom<Transaction>(
            this.http.get<Transaction>(`${environment.apiUrl}/transaction/${id}`),
        );
    }

    public updateTransaction(id: number, transaction: Transaction): Promise<Transaction> {
        return firstValueFrom<Transaction>(
            this.http.put<Transaction>(`${environment.apiUrl}/transaction/${id}`, transaction),
        );
    }

    public deleteTransaction(id: number): Promise<OkResponse> {
        return firstValueFrom<OkResponse>(this.http.delete<OkResponse>(`${environment.apiUrl}/transaction/${id}`));
    }
}
