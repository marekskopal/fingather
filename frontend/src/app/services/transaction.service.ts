import { HttpClient } from '@angular/common/http';
import {inject, Injectable} from '@angular/core';
import { Transaction, TransactionActionType } from '@app/models';
import { OrderDirection } from '@app/models/enums/order-direction';
import { TransactionOrderBy } from '@app/models/enums/transaction-order-by';
import { OkResponse } from '@app/models/ok-response';
import { TransactionList } from '@app/models/transaction-list';
import { NotifyService } from '@app/services/notify-service';
import {DownloadUtils} from "@app/utils/download-utils";
import { buildHttpParams } from '@app/utils/http-params-builder';
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
        orderBy: TransactionOrderBy | null = null,
        orderDirection: OrderDirection | null = null,
    ): Promise<TransactionList> {
        return firstValueFrom<TransactionList>(
            this.http.get<TransactionList>(`${environment.apiUrl}/transactions/${portfolioId}`, {
                params: buildHttpParams({
                    assetId,
                    actionTypes: actionTypes !== null ? actionTypes.join('|') : null,
                    search,
                    created,
                    limit,
                    offset,
                    orderBy,
                    orderDirection,
                }),
            }),
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

    public async exportCsv(
        portfolioId: number,
        assetId: number | null = null,
        actionTypes: TransactionActionType[] | null = null,
        search: string | null = null,
        created: string | null = null,
    ): Promise<void> {
        const blob = await firstValueFrom(
            this.http.get(`${environment.apiUrl}/transactions/${portfolioId}/export-csv`, {
                params: buildHttpParams({
                    assetId,
                    actionTypes: actionTypes !== null ? actionTypes.join('|') : null,
                    search,
                    created,
                }),
                responseType: 'blob',
            }),
        );
        DownloadUtils.downloadBlob(blob, 'transactions.csv');
    }

    public async exportXlsx(
        portfolioId: number,
        assetId: number | null = null,
        actionTypes: TransactionActionType[] | null = null,
        search: string | null = null,
        created: string | null = null,
    ): Promise<void> {
        const blob = await firstValueFrom(
            this.http.get(`${environment.apiUrl}/transactions/${portfolioId}/export-xlsx`, {
                params: buildHttpParams({
                    assetId,
                    actionTypes: actionTypes !== null ? actionTypes.join('|') : null,
                    search,
                    created,
                }),
                responseType: 'blob',
            }),
        );
        DownloadUtils.downloadBlob(blob, 'transactions.xlsx');
    }
}
