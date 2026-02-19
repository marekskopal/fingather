import {AsyncPipe, DatePipe, DecimalPipe} from "@angular/common";
import {
    ChangeDetectionStrategy, Component, computed, inject, input, OnInit,
} from '@angular/core';
import {Currency, Goal} from "@app/models";
import {GoalTypeEnum} from "@app/models/enums/goal-type-enum";
import {CurrencyService} from "@app/services";
import {MoneyPipe} from "@app/shared/pipes/money.pipe";
import { ColorEnum } from '@app/utils/enum/color-enum';
import {TranslatePipe} from "@ngx-translate/core";

@Component({
    selector: 'fingather-goal-progress-bar',
    templateUrl: 'goal-progress-bar.component.html',
    changeDetection: ChangeDetectionStrategy.OnPush,
    imports: [
        DatePipe,
        TranslatePipe,
        DecimalPipe,
        MoneyPipe,
        AsyncPipe,
    ],
})
export class GoalProgressBarComponent implements OnInit {
    private readonly currencyService = inject(CurrencyService);

    public readonly goal = input.required<Goal>();
    public readonly index = input<number>(0);
    public readonly showText = input<boolean>(false);

    protected readonly clampedPercentage = computed(() => Math.min(100, Math.max(0, this.goal().progressPercentage)));

    protected readonly barColor = computed<string>(() => {
        const colors = [
            ColorEnum.colorChart1,
            ColorEnum.colorChart2,
            ColorEnum.colorChart3,
            ColorEnum.colorChart4,
            ColorEnum.colorChart5,
        ];
        return colors[this.index() % colors.length];
    });

    protected readonly trackWidth = computed<string>(() => `${this.clampedPercentage()}%`);

    protected defaultCurrency: Currency;

    public async ngOnInit(): Promise<void> {
        this.defaultCurrency = await this.currencyService.getDefaultCurrency();
    }

    protected formatValue(value: string): string {
        return parseFloat(value).toFixed(2);
    }

    protected readonly GoalTypeEnum = GoalTypeEnum;
}
