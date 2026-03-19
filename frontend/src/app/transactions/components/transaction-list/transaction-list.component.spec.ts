import { NO_ERRORS_SCHEMA } from '@angular/core';
import { ComponentFixture, TestBed } from '@angular/core/testing';
import { provideRouter } from '@angular/router';
import { TransactionActionType, TransactionCreateType } from '@app/models';
import { OrderDirection } from '@app/models/enums/order-direction';
import { TransactionOrderBy } from '@app/models/enums/transaction-order-by';
import { TickerTypeEnum } from '@app/models/enums/ticker-type-enum';
import { Market } from '@app/models/market';
import { Ticker } from '@app/models/ticker';
import { Transaction } from '@app/models/transaction';
import { TransactionList } from '@app/models/transaction-list';
import { PortfolioService, TransactionService } from '@app/services';
import { TranslateModule } from '@ngx-translate/core';

import { TransactionListComponent } from './transaction-list.component';

const mockMarket: Market = {
    id: 1, name: 'NYSE', acronym: 'NYSE', mic: 'XNYS',
    country: 'US', city: 'New York', timezone: 'America/New_York', currencyId: 1,
};

const mockTicker: Ticker = {
    id: 1, ticker: 'AAPL', name: 'Apple Inc.', marketId: 1, currencyId: 1,
    type: TickerTypeEnum.Stock, isin: null, logo: null, sector: null, industry: null,
    website: null, description: null, country: null, market: mockMarket,
};

function makeTransaction(id: number): Transaction {
    return {
        id,
        assetId: String(id * 10),
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
}

const mockPortfolio = { id: 1, name: 'Test Portfolio', currencyId: 1, isDefault: true };

describe('TransactionListComponent', () => {
    let fixture: ComponentFixture<TransactionListComponent>;
    let component: TransactionListComponent;
    let transactionService: { getTransactions: ReturnType<typeof vi.fn>; deleteTransaction: ReturnType<typeof vi.fn>; subscribe: ReturnType<typeof vi.fn>; notify: ReturnType<typeof vi.fn> };
    let portfolioService: { getCurrentPortfolio: ReturnType<typeof vi.fn>; subscribe: ReturnType<typeof vi.fn> };

    beforeEach(async () => {
        transactionService = {
            getTransactions: vi.fn().mockResolvedValue({ transactions: [], count: 0 } as TransactionList),
            deleteTransaction: vi.fn().mockResolvedValue({ code: 200, message: 'ok' }),
            subscribe: vi.fn(),
            notify: vi.fn(),
        };

        portfolioService = {
            getCurrentPortfolio: vi.fn().mockResolvedValue(mockPortfolio),
            subscribe: vi.fn(),
        };

        await TestBed.configureTestingModule({
            imports: [TransactionListComponent, TranslateModule.forRoot()],
            providers: [
                { provide: TransactionService, useValue: transactionService },
                { provide: PortfolioService, useValue: portfolioService },
                provideRouter([]),
            ],
            schemas: [NO_ERRORS_SCHEMA],
        }).compileComponents();

        fixture = TestBed.createComponent(TransactionListComponent);
        component = fixture.componentInstance;
    });

    it('should create', () => {
        expect(component).toBeTruthy();
    });

    it('should call portfolio service and transaction service on init', async () => {
        fixture.detectChanges(); // triggers ngOnInit
        await fixture.whenStable();

        expect(portfolioService.getCurrentPortfolio).toHaveBeenCalled();
        expect(transactionService.getTransactions).toHaveBeenCalledWith(
            mockPortfolio.id,
            null,   // assetId
            null,   // actionTypes from input
            null,   // search
            null,   // created
            50,     // pageSize
            0,      // offset (page 1)
            TransactionOrderBy.ActionCreated,
            OrderDirection.Desc,
        );
    });

    it('should set transactionList after refresh', async () => {
        const mockList: TransactionList = { transactions: [makeTransaction(1)], count: 1 };
        transactionService.getTransactions.mockResolvedValue(mockList);

        fixture.detectChanges();
        await fixture.whenStable();
        // Signal update may need an extra microtask flush after the promise resolves
        await new Promise(resolve => setTimeout(resolve, 0));

        expect(component['transactionList']()).toEqual(mockList);
    });

    it('should reset transactionList to null before fetching', async () => {
        // On the first detectChanges, ngOnInit calls refreshTransactions which sets to null then resolves
        const snapshots: Array<TransactionList | null> = [];
        let resolveGet!: (value: TransactionList) => void;
        const pendingPromise = new Promise<TransactionList>((resolve) => {
            resolveGet = resolve;
        });
        transactionService.getTransactions.mockReturnValue(pendingPromise);

        fixture.detectChanges();

        // While the promise is pending, transactionList should be null
        snapshots.push(component['transactionList']());
        expect(snapshots[0]).toBeNull();

        resolveGet({ transactions: [], count: 0 });
        await fixture.whenStable();
    });

    it('should call deleteTransaction and refresh when deleteTransaction is called', async () => {
        const mockList: TransactionList = { transactions: [makeTransaction(42)], count: 1 };
        transactionService.getTransactions.mockResolvedValue(mockList);

        fixture.detectChanges();
        await fixture.whenStable();
        await new Promise(resolve => setTimeout(resolve, 0));

        // transactionList should now contain transaction 42
        transactionService.getTransactions.mockClear();

        await component['deleteTransaction'](42);
        await fixture.whenStable();

        expect(transactionService.deleteTransaction).toHaveBeenCalledWith(42);
        expect(transactionService.getTransactions).toHaveBeenCalled();
    });

    it('should not call deleteTransaction when transaction id not found', async () => {
        const mockList: TransactionList = { transactions: [makeTransaction(1)], count: 1 };
        transactionService.getTransactions.mockResolvedValue(mockList);

        fixture.detectChanges();
        await fixture.whenStable();

        await component['deleteTransaction'](999); // does not exist

        expect(transactionService.deleteTransaction).not.toHaveBeenCalled();
    });

    it('should update page and refresh when changePage is called', async () => {
        fixture.detectChanges();
        await fixture.whenStable();
        transactionService.getTransactions.mockClear();

        await component['changePage'](3);
        await fixture.whenStable();

        // offset = (3 - 1) * 50 = 100
        expect(transactionService.getTransactions).toHaveBeenCalledWith(
            mockPortfolio.id,
            null, null, null, null,
            50,
            100, // page 3
            TransactionOrderBy.ActionCreated,
            OrderDirection.Desc,
        );
    });

    it('should update sort direction when same column is clicked again', async () => {
        fixture.detectChanges();
        await fixture.whenStable();
        transactionService.getTransactions.mockClear();

        // First call: sort by ActionCreated Desc → Asc
        await component['changeSort'](TransactionOrderBy.ActionCreated);
        await fixture.whenStable();

        expect(transactionService.getTransactions).toHaveBeenCalledWith(
            mockPortfolio.id,
            null, null, null, null,
            50, 0,
            TransactionOrderBy.ActionCreated,
            OrderDirection.Asc,
        );
    });
});
