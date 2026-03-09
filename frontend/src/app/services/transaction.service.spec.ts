import { provideHttpClient } from '@angular/common/http';
import { HttpTestingController, provideHttpClientTesting } from '@angular/common/http/testing';
import { TestBed } from '@angular/core/testing';
import { Transaction, TransactionActionType, TransactionCreateType } from '@app/models';
import { OrderDirection } from '@app/models/enums/order-direction';
import { TickerTypeEnum } from '@app/models/enums/ticker-type-enum';
import { TransactionOrderBy } from '@app/models/enums/transaction-order-by';
import { Market } from '@app/models/market';
import { OkResponse } from '@app/models/ok-response';
import { Ticker } from '@app/models/ticker';
import { TransactionList } from '@app/models/transaction-list';
import { DownloadUtils } from '@app/utils/download-utils';
import { environment } from '@environments/environment';

import { TransactionService } from './transaction.service';

const mockMarket: Market = {
    id: 1, name: 'NYSE', acronym: 'NYSE', mic: 'XNYS',
    country: 'US', city: 'New York', timezone: 'America/New_York', currencyId: 1,
};

const mockTicker: Ticker = {
    id: 1, ticker: 'AAPL', name: 'Apple Inc.', marketId: 1, currencyId: 1,
    type: TickerTypeEnum.Stock, isin: null, logo: null, sector: null, industry: null,
    website: null, description: null, country: null, market: mockMarket,
};

const mockTransaction: Transaction = {
    id: 1,
    assetId: '10',
    brokerId: null,
    actionType: TransactionActionType.Buy,
    actionCreated: '2024-01-01T00:00:00Z',
    createType: TransactionCreateType.Manual,
    created: new Date('2024-01-01'),
    modified: new Date('2024-01-01'),
    units: '10',
    price: '150',
    currencyId: 1,
    tax: '0',
    taxCurrencyId: 1,
    fee: '0',
    feeCurrencyId: 1,
    notes: '',
    importIdentifier: '',
    ticker: mockTicker,
};

const mockTransactionList: TransactionList = { transactions: [mockTransaction], count: 1 };

describe('TransactionService', () => {
    let service: TransactionService;
    let httpMock: HttpTestingController;

    beforeEach(() => {
        TestBed.configureTestingModule({
            providers: [TransactionService, provideHttpClient(), provideHttpClientTesting()],
        });
        service = TestBed.inject(TransactionService);
        httpMock = TestBed.inject(HttpTestingController);
    });

    afterEach(() => {
        httpMock.verify();
    });

    describe('createTransaction', () => {
        it('POSTs to /transactions/:portfolioId and returns the transaction', async () => {
            const promise = service.createTransaction(mockTransaction, 1);

            const req = httpMock.expectOne(`${environment.apiUrl}/transactions/1`);
            expect(req.request.method).toBe('POST');
            expect(req.request.body).toEqual(mockTransaction);
            req.flush(mockTransaction);

            expect(await promise).toEqual(mockTransaction);
        });
    });

    describe('getTransactions', () => {
        it('GETs /transactions/:portfolioId with no extra params when all optional are null', async () => {
            const promise = service.getTransactions(1);

            const req = httpMock.expectOne(`${environment.apiUrl}/transactions/1`);
            expect(req.request.method).toBe('GET');
            expect(req.request.params.keys()).toHaveLength(0);
            req.flush(mockTransactionList);

            expect(await promise).toEqual(mockTransactionList);
        });

        it('sends assetId param when provided', async () => {
            const promise = service.getTransactions(1, 10);

            const req = httpMock.expectOne((r) => r.url === `${environment.apiUrl}/transactions/1`);
            expect(req.request.params.get('assetId')).toBe('10');
            req.flush(mockTransactionList);

            await promise;
        });

        it('sends actionTypes joined with | when provided', async () => {
            const promise = service.getTransactions(
                1, null, [TransactionActionType.Buy, TransactionActionType.Sell],
            );

            const req = httpMock.expectOne((r) => r.url === `${environment.apiUrl}/transactions/1`);
            expect(req.request.params.get('actionTypes')).toBe(
                `${TransactionActionType.Buy}|${TransactionActionType.Sell}`,
            );
            req.flush(mockTransactionList);

            await promise;
        });

        it('sends search, created, limit, offset, orderBy, orderDirection when provided', async () => {
            const promise = service.getTransactions(
                1, null, null, 'AAPL', '2024-01-01', 10, 20,
                TransactionOrderBy.ActionCreated, OrderDirection.Desc,
            );

            const req = httpMock.expectOne((r) => r.url === `${environment.apiUrl}/transactions/1`);
            expect(req.request.params.get('search')).toBe('AAPL');
            expect(req.request.params.get('created')).toBe('2024-01-01');
            expect(req.request.params.get('limit')).toBe('10');
            expect(req.request.params.get('offset')).toBe('20');
            expect(req.request.params.get('orderBy')).toBe(TransactionOrderBy.ActionCreated);
            expect(req.request.params.get('orderDirection')).toBe(OrderDirection.Desc);
            req.flush(mockTransactionList);

            await promise;
        });
    });

    describe('getTransaction', () => {
        it('GETs /transaction/:id', async () => {
            const promise = service.getTransaction(1);

            const req = httpMock.expectOne(`${environment.apiUrl}/transaction/1`);
            expect(req.request.method).toBe('GET');
            req.flush(mockTransaction);

            expect(await promise).toEqual(mockTransaction);
        });
    });

    describe('updateTransaction', () => {
        it('PUTs to /transaction/:id', async () => {
            const promise = service.updateTransaction(1, mockTransaction);

            const req = httpMock.expectOne(`${environment.apiUrl}/transaction/1`);
            expect(req.request.method).toBe('PUT');
            expect(req.request.body).toEqual(mockTransaction);
            req.flush(mockTransaction);

            expect(await promise).toEqual(mockTransaction);
        });
    });

    describe('deleteTransaction', () => {
        it('DELETEs /transaction/:id', async () => {
            const ok: OkResponse = { code: 200, message: 'ok' };
            const promise = service.deleteTransaction(1);

            const req = httpMock.expectOne(`${environment.apiUrl}/transaction/1`);
            expect(req.request.method).toBe('DELETE');
            req.flush(ok);

            expect(await promise).toEqual(ok);
        });
    });

    describe('exportCsv', () => {
        it('GETs the CSV export endpoint and triggers download', async () => {
            const downloadSpy = vi.spyOn(DownloadUtils, 'downloadBlob').mockReturnValue();

            const promise = service.exportCsv(1);

            const req = httpMock.expectOne((r) => r.url === `${environment.apiUrl}/transactions/1/export-csv`);
            expect(req.request.method).toBe('GET');
            req.flush(new Blob(['csv'], { type: 'text/csv' }));

            await promise;
            expect(downloadSpy).toHaveBeenCalledWith(expect.any(Blob), 'transactions.csv');

            downloadSpy.mockRestore();
        });

        it('sends optional filters as query params', async () => {
            const downloadSpy = vi.spyOn(DownloadUtils, 'downloadBlob').mockReturnValue();

            const promise = service.exportCsv(1, 10, [TransactionActionType.Buy], 'AAPL', '2024-01-01');

            const req = httpMock.expectOne((r) => r.url === `${environment.apiUrl}/transactions/1/export-csv`);
            expect(req.request.params.get('assetId')).toBe('10');
            expect(req.request.params.get('actionTypes')).toBe(TransactionActionType.Buy);
            expect(req.request.params.get('search')).toBe('AAPL');
            expect(req.request.params.get('created')).toBe('2024-01-01');
            req.flush(new Blob());

            await promise;
            downloadSpy.mockRestore();
        });
    });

    describe('exportXlsx', () => {
        it('GETs the XLSX export endpoint and triggers download', async () => {
            const downloadSpy = vi.spyOn(DownloadUtils, 'downloadBlob').mockReturnValue();

            const promise = service.exportXlsx(1);

            const req = httpMock.expectOne((r) => r.url === `${environment.apiUrl}/transactions/1/export-xlsx`);
            expect(req.request.method).toBe('GET');
            req.flush(new Blob(['xlsx']));

            await promise;
            expect(downloadSpy).toHaveBeenCalledWith(expect.any(Blob), 'transactions.xlsx');

            downloadSpy.mockRestore();
        });
    });

    describe('NotifyService integration', () => {
        it('calls subscribers when notify() is invoked', () => {
            const cb = vi.fn();
            service.subscribe(cb);
            service.notify();
            expect(cb).toHaveBeenCalledTimes(1);
        });
    });
});
