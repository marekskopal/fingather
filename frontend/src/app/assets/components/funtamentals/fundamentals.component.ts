import {
    ChangeDetectionStrategy,
    Component, input, InputSignal, OnInit, signal
} from '@angular/core';
import { TickerFundamental } from '@app/models/ticker-fundamental';
import { TickerFundamentalService } from '@app/services/ticker-fundamental.service';

@Component({
    templateUrl: 'fundamentals.component.html',
    selector: 'fingather-ticker-fundamentals',
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class FundamentalsComponent implements OnInit {
    public tickerId: InputSignal<number> = input.required<number>();

    protected $tickerFundamental = signal<TickerFundamental | null>(null);

    public constructor(
        private readonly tickerFundamentalService: TickerFundamentalService,
    ) {
    }

    public async ngOnInit(): Promise<void> {
        const tickerFundamental = await this.tickerFundamentalService.getTickerFundamental(this.tickerId());
        this.$tickerFundamental.set(tickerFundamental);
    }

    protected get tickerFundamental(): TickerFundamental | null {
        return this.$tickerFundamental();
    }
}
