import { NO_ERRORS_SCHEMA } from '@angular/core';
import { ComponentFixture, TestBed } from '@angular/core/testing';
import { ActivatedRoute } from '@angular/router';
import { DcaPlan } from '@app/models';
import { DcaPlanTargetTypeEnum } from '@app/models/enums/dca-plan-target-type-enum';
import { DcaPlanService } from '@app/services';
import { TranslateModule } from '@ngx-translate/core';

import { DcaPlanDetailComponent } from './dca-plan-detail.component';

const mockDcaPlan: DcaPlan = {
    id: 1,
    targetType: DcaPlanTargetTypeEnum.Asset,
    portfolioId: 1,
    assetId: 5,
    groupId: null,
    strategyId: null,
    targetName: 'AAPL',
    amount: '500.00',
    currencyId: 1,
    intervalMonths: 1,
    startDate: '2024-01-01',
    endDate: null,
    annualReturnRate: 7,
    monthlyReturnRate: 0.583,
    createdAt: '2024-01-01T00:00:00.000Z',
};

describe('DcaPlanDetailComponent', () => {
    describe('when route has a valid id', () => {
        let fixture: ComponentFixture<DcaPlanDetailComponent>;
        let component: DcaPlanDetailComponent;
        let dcaPlanServiceSpy: { getDcaPlan: ReturnType<typeof vi.fn> };

        beforeEach(async () => {
            dcaPlanServiceSpy = { getDcaPlan: vi.fn().mockResolvedValue(mockDcaPlan) };

            await TestBed.configureTestingModule({
                imports: [DcaPlanDetailComponent, TranslateModule.forRoot()],
                providers: [
                    { provide: DcaPlanService, useValue: dcaPlanServiceSpy },
                    { provide: ActivatedRoute, useValue: { snapshot: { params: { id: '1' } } } },
                ],
                schemas: [NO_ERRORS_SCHEMA],
            }).compileComponents();

            fixture = TestBed.createComponent(DcaPlanDetailComponent);
            component = fixture.componentInstance;
        });

        it('should create', () => {
            expect(component).toBeTruthy();
        });

        it('should fetch the plan by id on init', async () => {
            await component.ngOnInit();
            expect(dcaPlanServiceSpy.getDcaPlan).toHaveBeenCalledWith(1);
        });

        it('should set the plan signal after init', async () => {
            await component.ngOnInit();
            expect(component['plan']()).toEqual(mockDcaPlan);
        });
    });

    describe('when route has no id', () => {
        let fixture: ComponentFixture<DcaPlanDetailComponent>;
        let component: DcaPlanDetailComponent;
        let dcaPlanServiceSpy: { getDcaPlan: ReturnType<typeof vi.fn> };

        beforeEach(async () => {
            dcaPlanServiceSpy = { getDcaPlan: vi.fn() };

            await TestBed.configureTestingModule({
                imports: [DcaPlanDetailComponent, TranslateModule.forRoot()],
                providers: [
                    { provide: DcaPlanService, useValue: dcaPlanServiceSpy },
                    { provide: ActivatedRoute, useValue: { snapshot: { params: {} } } },
                ],
                schemas: [NO_ERRORS_SCHEMA],
            }).compileComponents();

            fixture = TestBed.createComponent(DcaPlanDetailComponent);
            component = fixture.componentInstance;
        });

        it('should not call getDcaPlan', async () => {
            await component.ngOnInit();
            expect(dcaPlanServiceSpy.getDcaPlan).not.toHaveBeenCalled();
        });

        it('should leave plan signal as null', async () => {
            await component.ngOnInit();
            expect(component['plan']()).toBeNull();
        });
    });
});
