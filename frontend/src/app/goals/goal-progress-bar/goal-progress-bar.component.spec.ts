import { NO_ERRORS_SCHEMA } from '@angular/core';
import { ComponentFixture, TestBed } from '@angular/core/testing';
import { Goal } from '@app/models';
import { GoalTypeEnum } from '@app/models/enums/goal-type-enum';
import { CurrencyService } from '@app/services';
import { ColorEnum } from '@app/utils/enum/color-enum';
import { provideTranslateService } from '@ngx-translate/core';

import { GoalProgressBarComponent } from './goal-progress-bar.component';

const mockCurrency = { id: 1, code: 'USD', name: 'US Dollar', symbol: '$' };

function makeGoal(progressPercentage: number): Goal {
    return {
        id: 1,
        portfolioId: 1,
        portfolioName: 'Test Portfolio',
        type: GoalTypeEnum.PortfolioValue,
        targetValue: '10000',
        deadline: null,
        isActive: true,
        achievedAt: null,
        currentValue: '5000',
        progressPercentage,
        createdAt: '2024-01-01',
    };
}

function buildComponent(goal: Goal, index = 0): ComponentFixture<GoalProgressBarComponent> {
    const currencyServiceSpy = { getDefaultCurrency: vi.fn().mockResolvedValue(mockCurrency) };

    TestBed.configureTestingModule({
        imports: [GoalProgressBarComponent],
        providers: [provideTranslateService(), { provide: CurrencyService, useValue: currencyServiceSpy }],
        schemas: [NO_ERRORS_SCHEMA],
    }).compileComponents();

    const fixture = TestBed.createComponent(GoalProgressBarComponent);
    fixture.componentRef.setInput('goal', goal);
    fixture.componentRef.setInput('index', index);
    return fixture;
}

describe('GoalProgressBarComponent', () => {
    it('should create', () => {
        const fixture = buildComponent(makeGoal(50));
        expect(fixture.componentInstance).toBeTruthy();
    });

    describe('clampedPercentage', () => {
        it('returns the raw percentage when between 0 and 100', () => {
            const fixture = buildComponent(makeGoal(75));
            expect(fixture.componentInstance['clampedPercentage']()).toBe(75);
        });

        it('clamps to 100 when progressPercentage exceeds 100', () => {
            const fixture = buildComponent(makeGoal(150));
            expect(fixture.componentInstance['clampedPercentage']()).toBe(100);
        });

        it('clamps to 0 when progressPercentage is negative', () => {
            const fixture = buildComponent(makeGoal(-10));
            expect(fixture.componentInstance['clampedPercentage']()).toBe(0);
        });

        it('returns 0 when progressPercentage is 0', () => {
            const fixture = buildComponent(makeGoal(0));
            expect(fixture.componentInstance['clampedPercentage']()).toBe(0);
        });

        it('returns 100 when progressPercentage is exactly 100', () => {
            const fixture = buildComponent(makeGoal(100));
            expect(fixture.componentInstance['clampedPercentage']()).toBe(100);
        });
    });

    describe('trackWidth', () => {
        it('returns percentage string', () => {
            const fixture = buildComponent(makeGoal(42));
            expect(fixture.componentInstance['trackWidth']()).toBe('42%');
        });

        it('is capped at 100% even if progress exceeds 100', () => {
            const fixture = buildComponent(makeGoal(200));
            expect(fixture.componentInstance['trackWidth']()).toBe('100%');
        });
    });

    describe('barColor', () => {
        const colors = [
            ColorEnum.colorChart1,
            ColorEnum.colorChart2,
            ColorEnum.colorChart3,
            ColorEnum.colorChart4,
            ColorEnum.colorChart5,
        ];

        it.each([0, 1, 2, 3, 4])('returns the correct color for index %i', (index) => {
            const fixture = buildComponent(makeGoal(50), index);
            expect(fixture.componentInstance['barColor']()).toBe(colors[index]);
        });

        it('wraps around using modulo for index >= 5', () => {
            const fixture = buildComponent(makeGoal(50), 5);
            expect(fixture.componentInstance['barColor']()).toBe(colors[0]);
        });
    });

    describe('loading signal', () => {
        it('starts as true before ngOnInit resolves', () => {
            const fixture = buildComponent(makeGoal(50));
            // loading is true before async init completes
            expect(fixture.componentInstance['loading']()).toBe(true);
        });

        it('is set to false after ngOnInit resolves', async () => {
            const fixture = buildComponent(makeGoal(50));
            await fixture.componentInstance.ngOnInit();
            expect(fixture.componentInstance['loading']()).toBe(false);
        });
    });
});
