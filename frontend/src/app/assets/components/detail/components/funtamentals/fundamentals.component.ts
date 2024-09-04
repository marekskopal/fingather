import {
    ChangeDetectionStrategy,
    Component, inject, input, InputSignal, OnInit, signal
} from '@angular/core';
import {
    FundamentalRowComponent
} from "@app/assets/components/detail/components/funtamentals/components/fundamental-row/fundamental-row.component";
import {FundamentalsTabEnum} from "@app/assets/components/detail/components/funtamentals/types/fundamentals-tab-enum";
import { TickerFundamental } from '@app/models/ticker-fundamental';
import { TickerFundamentalService } from '@app/services/ticker-fundamental.service';
import {NgbNav, NgbNavContent, NgbNavItem, NgbNavLinkButton, NgbNavOutlet} from "@ng-bootstrap/ng-bootstrap";
import {TranslateModule} from "@ngx-translate/core";

@Component({
    templateUrl: 'fundamentals.component.html',
    selector: 'fingather-ticker-fundamentals',
    standalone: true,
    imports: [
        NgbNavContent,
        NgbNav,
        NgbNavLinkButton,
        NgbNavItem,
        TranslateModule,
        FundamentalRowComponent,
        NgbNavOutlet
    ],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class FundamentalsComponent implements OnInit {
    private readonly tickerFundamentalService = inject(TickerFundamentalService);

    public tickerId: InputSignal<number> = input.required<number>();

    protected $tickerFundamental = signal<TickerFundamental | null>(null);

    protected activeTab: FundamentalsTabEnum = FundamentalsTabEnum.ValuationsMetrics;
    protected readonly FundamentalsTabEnum = FundamentalsTabEnum;

    public async ngOnInit(): Promise<void> {
        const tickerFundamental = await this.tickerFundamentalService.getTickerFundamental(this.tickerId());
        this.$tickerFundamental.set(tickerFundamental);
    }

    protected get tickerFundamental(): TickerFundamental | null {
        return this.$tickerFundamental();
    }
}
