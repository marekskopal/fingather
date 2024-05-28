import {
    ChangeDetectionStrategy,
    Component, input, InputSignal, OnInit
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

    public tickerFundamental: TickerFundamental | null = null;

    public constructor(
        private readonly tickerFundamentalService: TickerFundamentalService,
    ) {
    }

    public async ngOnInit(): Promise<void> {
        this.tickerFundamental = await this.tickerFundamentalService.getTickerFundamental(this.tickerId());
    }
}
