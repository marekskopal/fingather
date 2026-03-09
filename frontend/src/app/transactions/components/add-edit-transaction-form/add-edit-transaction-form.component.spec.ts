import { NO_ERRORS_SCHEMA } from '@angular/core';
import { ComponentFixture, TestBed } from '@angular/core/testing';
import { UntypedFormBuilder } from '@angular/forms';
import { Router } from '@angular/router';
import { ActivatedRoute } from '@angular/router';
import { TransactionActionType } from '@app/models';
import {
    AlertService,
    AssetService,
    BrokerService,
    CurrencyService,
    PortfolioService,
    TransactionService,
} from '@app/services';
import { TranslateModule } from '@ngx-translate/core';

import { AddEditTransactionFormComponent } from './add-edit-transaction-form.component';

describe('AddEditTransactionFormComponent', () => {
    let fixture: ComponentFixture<AddEditTransactionFormComponent>;
    let component: AddEditTransactionFormComponent;

    beforeEach(async () => {
        await TestBed.configureTestingModule({
            imports: [AddEditTransactionFormComponent, TranslateModule.forRoot()],
            providers: [
                {
                    provide: TransactionService,
                    useValue: {
                        getTransaction: vi.fn(),
                        createTransaction: vi.fn().mockResolvedValue({}),
                        updateTransaction: vi.fn().mockResolvedValue({}),
                        notify: vi.fn(),
                    },
                },
                { provide: AssetService, useValue: { getAssets: vi.fn().mockResolvedValue([]) } },
                { provide: BrokerService, useValue: { getBrokers: vi.fn().mockResolvedValue([]) } },
                {
                    provide: CurrencyService,
                    useValue: {
                        getCurrencies: vi.fn().mockResolvedValue([]),
                        getDefaultCurrency: vi.fn().mockResolvedValue({ id: 1, code: 'USD', name: 'US Dollar', symbol: '$' }),
                    },
                },
                {
                    provide: PortfolioService,
                    useValue: { getCurrentPortfolio: vi.fn().mockResolvedValue(
                        { id: 1, name: 'Test', currencyId: 1, isDefault: true },
                        ) },
                },
                { provide: AlertService, useValue: { clear: vi.fn(), success: vi.fn(), error: vi.fn() } },
                { provide: Router, useValue: { navigate: vi.fn() } },
                { provide: ActivatedRoute, useValue: { snapshot: { params: {} } } },
            ],
            schemas: [NO_ERRORS_SCHEMA],
        }).compileComponents();

        fixture = TestBed.createComponent(AddEditTransactionFormComponent);
        component = fixture.componentInstance;

        // Build the form manually to test processCreate/UpdateTransaction without going through ngOnInit
        const fb = TestBed.inject(UntypedFormBuilder);
        component['form'] = fb.group({
            assetId: ['5'],
            brokerId: ['2'],
            actionType: [TransactionActionType.Buy],
            actionCreated: ['2024-01-15T10:00'],
            units: ['1.5'],
            price: ['100.00'],
            currencyId: [1],
            tax: ['0.00'],
            taxCurrencyId: [1],
            fee: ['0.50'],
            feeCurrencyId: [1],
        });
    });

    it('should create', () => {
        expect(component).toBeTruthy();
    });

    describe('processCreateTransaction', () => {
        it('should parse assetId as an integer', () => {
            const result = component['processCreateTransaction']();
            expect(result.assetId).toBe(5 as unknown as string);
        });

        it('should convert actionCreated to ISO string', () => {
            const result = component['processCreateTransaction']();
            expect(result.actionCreated).toBe(new Date('2024-01-15T10:00').toJSON());
        });

        it('should convert numeric fields to strings', () => {
            const result = component['processCreateTransaction']();
            expect(result.units).toBe('1.5');
            expect(result.price).toBe('100.00');
            expect(result.tax).toBe('0.00');
            expect(result.fee).toBe('0.50');
        });

        it('should keep brokerId when it has a value', () => {
            const result = component['processCreateTransaction']();
            expect(result.brokerId).toBe('2');
        });

        it('should convert empty brokerId to null', () => {
            component['form'].patchValue({ brokerId: '' });
            const result = component['processCreateTransaction']();
            expect(result.brokerId).toBeNull();
        });
    });

    describe('processUpdateTransaction', () => {
        it('should leave assetId as the raw form value (no parseInt)', () => {
            const result = component['processUpdateTransaction']();
            expect(result.assetId).toBe('5');
        });

        it('should convert actionCreated to ISO string', () => {
            const result = component['processUpdateTransaction']();
            expect(result.actionCreated).toBe(new Date('2024-01-15T10:00').toJSON());
        });

        it('should convert numeric fields to strings', () => {
            const result = component['processUpdateTransaction']();
            expect(result.units).toBe('1.5');
            expect(result.price).toBe('100.00');
            expect(result.tax).toBe('0.00');
            expect(result.fee).toBe('0.50');
        });

        it('should convert empty brokerId to null', () => {
            component['form'].patchValue({ brokerId: '' });
            const result = component['processUpdateTransaction']();
            expect(result.brokerId).toBeNull();
        });
    });
});
