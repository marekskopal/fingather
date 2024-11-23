import {
    ChangeDetectionStrategy, Component, input,
} from '@angular/core';
import { TickerFundamental } from '@app/models/ticker-fundamental';
import {TranslateModule} from "@ngx-translate/core";

@Component({
    templateUrl: 'fundamental-row.component.html',
    selector: 'fingather-ticker-fundamentals-fundamental-row',
    imports: [
        TranslateModule,
    ],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class FundamentalRowComponent {
    public tickerFundamental = input.required<TickerFundamental>();
    public fundamentalName = input.required<string>();

    protected get isShown(): boolean {
        return this.value !== null;
    }

    protected get value(): number | string | null {
        const tickerFundamental = this.tickerFundamental();

        return tickerFundamental[this.fundamentalName() as keyof TickerFundamental];
    }
}
